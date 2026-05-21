<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use Illuminate\Http\JsonResponse;

class InvoiceReportController extends Controller
{
    use RespondsWithDataTables;

    public function index()
    {
        $customers = Customer::where('is_active', true)
            ->when(!auth()->user()->isSuperAdmin(), fn($q) => $q->where('institution_id', auth()->user()->institution_id))
            ->get();
            
        $suppliers = Supplier::where('is_active', true)
            ->when(!auth()->user()->isSuperAdmin(), fn($q) => $q->where('institution_id', auth()->user()->institution_id))
            ->get();

        return view('admin.invoice-reports.index', compact('customers', 'suppliers'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = Invoice::with(['customer', 'supplier'])->orderBy('id', 'desc');
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('institution_id', auth()->user()->institution_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        return $this->dataTablesJson(
            $request,
            $query,
            ['invoice_number', 'description'],
            function (Invoice $row) {
                if ($row->type === 'customer') {
                    $partyHtml = '<span class="badge bg-info">Customer</span> ' . ($row->customer->name ?? '-');
                } else {
                    $partyHtml = '<span class="badge bg-secondary">Supplier</span> ' . ($row->supplier->name ?? '-');
                }

                $statusClass = [
                    'pending' => 'bg-warning',
                    'paid' => 'bg-success',
                    'cancelled' => 'bg-danger'
                ][$row->status] ?? 'bg-secondary';
                $statusHtml = '<span class="badge ' . $statusClass . '">' . ucfirst($row->status) . '</span>';
                
                return [
                    'id' => $row->id,
                    'DT_RowIndex' => $row->id,
                    'invoice_number' => $row->invoice_number,
                    'invoice_date' => $row->invoice_date ? $row->invoice_date->format('d-m-Y') : '-',
                    'party' => $partyHtml,
                    'description' => $row->description ?? '-',
                    'total_amount' => $row->total_amount,
                    'status' => $statusHtml,
                ];
            }
        );
    }
}
