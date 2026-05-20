<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RequiresInstitutionContext;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseAttachment;
use App\Models\ExpenseType;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    use AppliesInstitutionScope;
    use RequiresInstitutionContext;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.expenses.index');
    }

    public function create(): View
    {
        return view('admin.expenses.create');
    }

    public function edit(Expense $expense): View
    {
        $this->guardInstitution($expense->institution_id);
        $expense->load('attachments', 'expenseType');

        return view('admin.expenses.edit', compact('expense'));
    }

    public function data(Request $request): JsonResponse
    {
        $q = Expense::query()->with('expenseType')->orderByDesc('expense_date');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['title', 'vendor', 'invoice_number', 'payment_method', 'payment_status'],
            fn (Expense $e) => [
                'id' => $e->id,
                'date' => $e->expense_date?->format('Y-m-d'),
                'type' => $e->expenseType?->name,
                'title' => $e->title,
                'amount' => (float) $e->amount,
                'tax' => (float) $e->tax,
                'total_amount' => (float) $e->total_amount,
                'vendor' => $e->vendor,
                'invoice_number' => $e->invoice_number,
                'payment_method' => $e->payment_method,
                'payment_status' => $e->payment_status,
            ]
        );
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $data = $this->validated($request);
        $type = ExpenseType::query()->findOrFail($data['expense_type_id']);
        abort_unless((int) $type->institution_id === $this->currentInstitutionId(), 403);

        $data['institution_id'] = $this->currentInstitutionId();
        $data['total_amount'] = round((float) $data['amount'] + (float) $data['tax'], 2);

        $expense = Expense::query()->create($data);
        $this->saveAttachments($request, $expense);
        $this->activityLog->log('expense.created', $expense);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Saved', 'id' => $expense->id]);
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Expense saved');
    }

    public function update(Request $request, Expense $expense): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->guardInstitution($expense->institution_id);
        $data = $this->validated($request);
        $type = ExpenseType::query()->findOrFail($data['expense_type_id']);
        abort_unless((int) $type->institution_id === $this->currentInstitutionId(), 403);

        $data['total_amount'] = round((float) $data['amount'] + (float) $data['tax'], 2);
        $expense->update($data);
        $this->saveAttachments($request, $expense);
        $this->activityLog->log('expense.updated', $expense);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Updated']);
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated');
    }

    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        $this->guardInstitution($expense->institution_id);
        $expense->load('attachments');
        foreach ($expense->attachments as $a) {
            Storage::disk('public')->delete($a->file_path);
        }
        $expense->delete();
        $this->activityLog->log('expense.deleted', $expense);

        return response()->json(['message' => 'Deleted']);
    }

    public function downloadAttachment(Expense $expense, ExpenseAttachment $attachment): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        $this->guardInstitution($expense->institution_id);
        abort_unless($attachment->expense_id === $expense->id, 404);

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'expense_type_id' => ['required', 'exists:expense_types,id'],
            'expense_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:191'],
            'amount' => ['required', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'vendor' => ['nullable', 'string', 'max:191'],
            'invoice_number' => ['nullable', 'string', 'max:191'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:191'],
            'notes' => ['nullable', 'string'],
            'payment_status' => ['required', 'in:paid,pending'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:5120'],
        ]);
        unset($data['attachments']);

        return $data;
    }

    private function saveAttachments(Request $request, Expense $expense): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            $path = $file->store('expenses', 'public');
            ExpenseAttachment::query()->create([
                'expense_id' => $expense->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }
    }

    private function guardInstitution(?int $institutionId): void
    {
        $u = auth()->user();
        if ($u->isSuperAdmin()) {
            return;
        }
        if ((int) $u->institution_id !== (int) $institutionId) {
            abort(403);
        }
    }
}
