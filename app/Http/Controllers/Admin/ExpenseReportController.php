<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\AppliesInstitutionScope;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseReportController extends Controller
{
    use AppliesInstitutionScope;

    public function index(): View
    {
        return view('admin.expense-reports.index');
    }

    public function data(Request $request): JsonResponse
    {
        $type = $request->input('type', 'monthly');
        $institutionId = $request->user()->institution_id;

        $base = Expense::query()
            ->when(! $request->user()->isSuperAdmin(), fn ($q) => $q->where('institution_id', $institutionId));

        if ($type === 'daily' && $request->filled('date')) {
            return response()->json([
                'data' => (clone $base)->whereDate('expense_date', $request->date('date'))->get()->map(fn (Expense $e) => [
                    'title' => $e->title,
                    'total' => (float) $e->total_amount,
                    'vendor' => $e->vendor,
                ]),
            ]);
        }

        if ($type === 'weekly') {
            $rows = (clone $base)->whereBetween('expense_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->selectRaw('DATE(expense_date) as d, SUM(total_amount) as total')
                ->groupBy('d')
                ->orderBy('d')
                ->get();
        } elseif ($type === 'yearly') {
            $rows = (clone $base)->whereYear('expense_date', now()->year)
                ->selectRaw('MONTH(expense_date) as m, SUM(total_amount) as total')
                ->groupBy('m')
                ->orderBy('m')
                ->get();
        } elseif ($type === 'vendor') {
            $rows = (clone $base)->selectRaw('vendor, SUM(total_amount) as total')
                ->whereNotNull('vendor')
                ->groupBy('vendor')
                ->orderByDesc(DB::raw('SUM(total_amount)'))
                ->limit(50)
                ->get();

            return response()->json(['data' => $rows]);
        } elseif ($type === 'payment') {
            $rows = (clone $base)->selectRaw('payment_method, SUM(total_amount) as total')
                ->whereNotNull('payment_method')
                ->groupBy('payment_method')
                ->orderByDesc(DB::raw('SUM(total_amount)'))
                ->get();

            return response()->json(['data' => $rows]);
        } elseif ($type === 'pending') {
            $rows = (clone $base)->where('payment_status', 'pending')->orderByDesc('expense_date')->limit(200)->get()->map(fn (Expense $e) => [
                'title' => $e->title,
                'date' => $e->expense_date?->format('Y-m-d'),
                'total' => (float) $e->total_amount,
            ]);

            return response()->json(['data' => $rows]);
        } else {
            $rows = (clone $base)->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)
                ->selectRaw('DATE(expense_date) as d, SUM(total_amount) as total')
                ->groupBy('d')
                ->orderBy('d')
                ->get();
        }

        return response()->json(['data' => $rows]);
    }
}
