<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    use RespondsWithDataTables;

    public function index()
    {
        return view('admin.invoices.index');
    }

    public function data(Request $request): JsonResponse
    {
                $query = Invoice::with(['customer', 'supplier'])
            ->orderBy('id', 'desc');
        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // Apply date range filter if provided
        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('institution_id', auth()->user()->institution_id);
        }

        return $this->dataTablesJson(
            $request,
            $query,
            ['invoice_number'],
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
                
                $actionHtml = '
                    <a href="' . route('admin.invoices.print', $row->id) . '" class="btn btn-sm btn-outline-info" target="_blank" title="Print"><i class="fa fa-print"></i></a>
                    <a href="' . route('admin.invoices.edit', $row->id) . '" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $row->id . '" title="Delete"><i class="fa fa-trash"></i></button>
                ';

                return [
                    'id' => $row->id,
                    'DT_RowIndex' => $row->id,
                    'invoice_number' => $row->invoice_number,
                    'invoice_date' => $row->invoice_date ? $row->invoice_date->format('d-m-Y') : '-',
                    'party' => $partyHtml,
                    'total_amount' => $row->total_amount,
                    'status' => $statusHtml,
                    'action' => $actionHtml,
                ];
            }
        );
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)
            ->when(!auth()->user()->isSuperAdmin(), fn($q) => $q->where('institution_id', auth()->user()->institution_id))
            ->get();
            
        $suppliers = Supplier::where('is_active', true)
            ->when(!auth()->user()->isSuperAdmin(), fn($q) => $q->where('institution_id', auth()->user()->institution_id))
            ->get();

        return view('admin.invoices.form', compact('customers', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:customer,supplier',
            'customer_id' => 'required_if:type,customer|nullable|exists:customers,id',
            'supplier_id' => 'required_if:type,supplier|nullable|exists:suppliers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,paid,cancelled',
            'notes' => 'nullable|string',
            'authorized_signatory' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $institution_id = auth()->user()->institution_id;

            // Generate Invoice Number (SIW-001 format)
            $lastInvoice = Invoice::orderBy('id', 'desc')->first();
            $nextNumber = 1;
            if ($lastInvoice && preg_match('/SIW-(\d+)/', $lastInvoice->invoice_number, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }
            $invoice_number = 'SIW-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $invoice = new Invoice();
            $invoice->institution_id = $institution_id;
            $invoice->invoice_number = $invoice_number;
            $invoice->type = $request->type;
            $invoice->customer_id = $request->type === 'customer' ? $request->customer_id : null;
            $invoice->supplier_id = $request->type === 'supplier' ? $request->supplier_id : null;
            $invoice->invoice_date = $request->invoice_date;
            $invoice->due_date = $request->due_date;
            $invoice->description = $request->description;
            $invoice->status = $request->status;
            $invoice->notes = $request->notes;
            $invoice->authorized_signatory = $request->authorized_signatory;
            
            $subtotal = 0;
            $invoice->save();

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;

                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                ]);
            }

            // For now, assuming tax is 0 or handled later, just setting total
            $tax = 0; // Or get from request
            $invoice->subtotal = $subtotal;
            $invoice->tax = $tax;
            $invoice->total_amount = $subtotal + $tax;
            $invoice->save();

            DB::commit();

            return redirect()->route('admin.invoices.index')->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');
        
        $customers = Customer::where('is_active', true)
            ->when(!auth()->user()->isSuperAdmin(), fn($q) => $q->where('institution_id', auth()->user()->institution_id))
            ->get();
            
        $suppliers = Supplier::where('is_active', true)
            ->when(!auth()->user()->isSuperAdmin(), fn($q) => $q->where('institution_id', auth()->user()->institution_id))
            ->get();

        return view('admin.invoices.form', compact('invoice', 'customers', 'suppliers'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'type' => 'required|in:customer,supplier',
            'customer_id' => 'required_if:type,customer|nullable|exists:customers,id',
            'supplier_id' => 'required_if:type,supplier|nullable|exists:suppliers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,paid,cancelled',
            'notes' => 'nullable|string',
            'authorized_signatory' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $invoice->type = $request->type;
            $invoice->customer_id = $request->type === 'customer' ? $request->customer_id : null;
            $invoice->supplier_id = $request->type === 'supplier' ? $request->supplier_id : null;
            $invoice->invoice_date = $request->invoice_date;
            $invoice->due_date = $request->due_date;
            $invoice->description = $request->description;
            $invoice->status = $request->status;
            $invoice->notes = $request->notes;
            $invoice->authorized_signatory = $request->authorized_signatory;
            
            // Delete old items
            $invoice->items()->delete();

            $subtotal = 0;
            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;

                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                ]);
            }

            $tax = 0; 
            $invoice->subtotal = $subtotal;
            $invoice->tax = $tax;
            $invoice->total_amount = $subtotal + $tax;
            $invoice->save();

            DB::commit();

            return redirect()->route('admin.invoices.index')->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json(['success' => true, 'message' => 'Invoice deleted successfully.']);
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'supplier', 'items', 'institution']);
        return view('admin.invoices.print', compact('invoice'));
    }
}
