<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Concerns\RequiresInstitutionContext;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Http\Controllers\Controller;
use App\Models\ExpenseType;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseTypeController extends Controller
{
    use AppliesInstitutionScope;
    use RequiresInstitutionContext;
    use RespondsWithDataTables;

    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        return view('admin.expense-types.index');
    }

    public function data(Request $request): JsonResponse
    {
        $q = ExpenseType::query()->orderByDesc('id');
        $this->filterInstitution($q);

        return $this->dataTablesJson(
            $request,
            $q,
            ['code', 'name', 'status'],
            fn (ExpenseType $t) => [
                'id' => $t->id,
                'code' => $t->code,
                'name' => $t->name,
                'status' => $t->status,
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['institution_id'] = $this->currentInstitutionId();
        $row = ExpenseType::query()->create($data);
        $this->activityLog->log('expense_type.created', $row);

        return response()->json(['message' => 'Saved', 'data' => $row]);
    }

    public function update(Request $request, ExpenseType $expense_type): JsonResponse
    {
        $this->guardInstitution($expense_type->institution_id);
        $expense_type->update($this->validated($request, $expense_type->id, $expense_type->institution_id));
        $this->activityLog->log('expense_type.updated', $expense_type);

        return response()->json(['message' => 'Updated']);
    }

    public function destroy(Request $request, ExpenseType $expense_type): JsonResponse
    {
        $this->guardInstitution($expense_type->institution_id);
        $expense_type->delete();
        $this->activityLog->log('expense_type.deleted', $expense_type);

        return response()->json(['message' => 'Deleted']);
    }

    private function validated(Request $request, ?int $id = null, ?int $institutionId = null): array
    {
        $institutionId ??= $request->user()->institution_id;

        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('expense_types', 'code')->where(fn ($q) => $q->where('institution_id', $institutionId))->ignore($id)],
            'name' => ['required', 'string', 'max:191'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
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
