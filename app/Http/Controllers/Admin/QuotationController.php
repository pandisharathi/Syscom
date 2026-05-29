<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotationMail;

class QuotationController extends Controller
{
    use RespondsWithDataTables;

    public function index()
    {
        return view('admin.quotations.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Quotation::with('customer')->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('quotation_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('quotation_date', '<=', $request->end_date);
        }
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('institution_id', auth()->user()->institution_id);
        }

        return $this->dataTablesJson(
            $request,
            $query,
            ['quotation_number'],
            function (Quotation $row) {
                $statusClass = [
                    'draft' => 'bg-secondary',
                    'final' => 'bg-primary',
                    'approved' => 'bg-success',
                    'rejected' => 'bg-danger',
                    'invoiced' => 'bg-info'
                ][$row->status] ?? 'bg-secondary';
                
                $statusHtml = '<span class="badge ' . $statusClass . '">' . ucfirst($row->status) . '</span>';
                
                $actionHtml = '
                    <a href="' . route('admin.quotations.show', $row->id) . '" class="btn btn-sm btn-outline-info" title="View"><i class="fa fa-eye"></i></a>
                    <a href="' . route('admin.quotations.edit', $row->id) . '" class="btn btn-sm btn-outline-primary ' . ($row->status !== 'draft' ? 'disabled' : '') . '" title="Edit"><i class="fa fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $row->id . '" title="Delete"><i class="fa fa-trash"></i></button>
                ';

                return [
                    'id' => $row->id,
                    'DT_RowIndex' => $row->id,
                    'quotation_number' => $row->quotation_number,
                    'quotation_date' => $row->quotation_date ? $row->quotation_date->format('d-m-Y') : '-',
                    'customer' => $row->customer->name ?? '-',
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
            
        return view('admin.quotations.form', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'terms_conditions' => 'nullable|string',
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

            // Generate Quotation Number (QT-001 format)
            $lastQuotation = Quotation::orderBy('id', 'desc')->first();
            $nextNumber = 1;
            if ($lastQuotation && preg_match('/QT-(\d+)/', $lastQuotation->quotation_number, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }
            $quotation_number = 'QT-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $quotation = new Quotation();
            $quotation->institution_id = $institution_id;
            $quotation->quotation_number = $quotation_number;
            $quotation->customer_id = $request->customer_id;
            $quotation->quotation_date = $request->quotation_date;
            $quotation->expiry_date = $request->expiry_date;
            $quotation->terms_conditions = $request->terms_conditions;
            $quotation->notes = $request->notes;
            $quotation->authorized_signatory = $request->authorized_signatory;
            $quotation->status = 'draft';
            
            $subtotal = 0;
            $quotation->save();

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;

                $quotation->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                ]);
            }

            $discount = $request->discount ?? 0;
            $tax = $request->tax ?? 0;
            
            $quotation->subtotal = $subtotal;
            $quotation->discount = $discount;
            $quotation->tax = $tax;
            $quotation->total_amount = $subtotal - $discount + $tax;
            $quotation->save();

            DB::commit();

            return redirect()->route('admin.quotations.index')->with('success', 'Quotation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'items', 'institution']);
        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        if ($quotation->status !== 'draft') {
            return redirect()->route('admin.quotations.index')->with('error', 'Only draft quotations can be edited.');
        }

        $quotation->load('items');
        
        $customers = Customer::where('is_active', true)
            ->when(!auth()->user()->isSuperAdmin(), fn($q) => $q->where('institution_id', auth()->user()->institution_id))
            ->get();

        return view('admin.quotations.form', compact('quotation', 'customers'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        if ($quotation->status !== 'draft') {
            return redirect()->route('admin.quotations.index')->with('error', 'Only draft quotations can be edited.');
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'authorized_signatory' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $quotation->customer_id = $request->customer_id;
            $quotation->quotation_date = $request->quotation_date;
            $quotation->expiry_date = $request->expiry_date;
            $quotation->terms_conditions = $request->terms_conditions;
            $quotation->notes = $request->notes;
            $quotation->authorized_signatory = $request->authorized_signatory;
            
            $quotation->items()->delete();

            $subtotal = 0;
            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;

                $quotation->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                ]);
            }

            $discount = $request->discount ?? 0;
            $tax = $request->tax ?? 0; 
            
            $quotation->subtotal = $subtotal;
            $quotation->discount = $discount;
            $quotation->tax = $tax;
            $quotation->total_amount = $subtotal - $discount + $tax;
            $quotation->save();

            DB::commit();

            return redirect()->route('admin.quotations.index')->with('success', 'Quotation updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return response()->json(['success' => true, 'message' => 'Quotation deleted successfully.']);
    }

    public function print(Quotation $quotation)
    {
        $quotation->load(['customer', 'items', 'institution']);
        return view('admin.quotations.print', compact('quotation'));
    }

    public function exportPdf(Quotation $quotation)
    {
        $quotation->load(['customer', 'items', 'institution']);
        $pdf = Pdf::loadView('admin.quotations.pdf', compact('quotation'));
        return $pdf->download('Quotation_'.$quotation->quotation_number.'.pdf');
    }

    public function duplicate(Quotation $quotation)
    {
        try {
            DB::beginTransaction();

            $lastQuotation = Quotation::orderBy('id', 'desc')->first();
            $nextNumber = 1;
            if ($lastQuotation && preg_match('/QT-(\d+)/', $lastQuotation->quotation_number, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }
            $quotation_number = 'QT-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $newQuotation = $quotation->replicate();
            $newQuotation->quotation_number = $quotation_number;
            $newQuotation->status = 'draft';
            $newQuotation->quotation_date = now();
            $newQuotation->expiry_date = now()->addDays(30);
            $newQuotation->save();

            foreach ($quotation->items as $item) {
                $newQuotation->items()->create($item->only(['description', 'quantity', 'unit_price', 'total']));
            }

            DB::commit();

            return redirect()->route('admin.quotations.edit', $newQuotation->id)->with('success', 'Quotation duplicated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error duplicating quotation: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $request->validate(['status' => 'required|in:final,approved,rejected']);
        $quotation->update(['status' => $request->status]);
        return back()->with('success', 'Status updated successfully.');
    }

    public function convertToInvoice(Quotation $quotation)
    {
        if ($quotation->status !== 'approved') {
            return back()->with('error', 'Only approved quotations can be converted to an invoice.');
        }

        try {
            DB::beginTransaction();

            // Generate Invoice Number (SIW-001 format)
            $lastInvoice = Invoice::orderBy('id', 'desc')->first();
            $nextNumber = 1;
            if ($lastInvoice && preg_match('/SIW-(\d+)/', $lastInvoice->invoice_number, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }
            $invoice_number = 'SIW-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $invoice = new Invoice();
            $invoice->institution_id = $quotation->institution_id;
            $invoice->invoice_number = $invoice_number;
            $invoice->type = 'customer';
            $invoice->customer_id = $quotation->customer_id;
            $invoice->quotation_id = $quotation->id;
            $invoice->invoice_date = now();
            $invoice->due_date = now()->addDays(15);
            $invoice->description = 'Invoice from Quotation ' . $quotation->quotation_number;
            $invoice->status = 'pending';
            $invoice->notes = $quotation->notes;
            $invoice->authorized_signatory = $quotation->authorized_signatory;
            
            // To convert, we subtract discount from subtotal, maybe just store as subtotal in invoice since invoice lacks a discount field.
            $invoice->subtotal = $quotation->subtotal - $quotation->discount;
            $invoice->tax = $quotation->tax;
            $invoice->total_amount = $quotation->total_amount;
            $invoice->save();

            // Invoice items
            foreach ($quotation->items as $item) {
                // We'll apply discount proportionally or just copy line items. Let's just copy them for now.
                $invoice->items()->create($item->only(['description', 'quantity', 'unit_price', 'total']));
            }

            $quotation->update(['status' => 'invoiced']);

            DB::commit();

            return redirect()->route('admin.invoices.show', $invoice->id)->with('success', 'Quotation converted to Invoice successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error converting to invoice: ' . $e->getMessage());
        }
    }

    public function sendEmail(Quotation $quotation)
    {
        try {
            if (!$quotation->customer->email) {
                return back()->with('error', 'Customer does not have an email address.');
            }
            
            Mail::to($quotation->customer->email)->send(new QuotationMail($quotation));

            return back()->with('success', 'Quotation emailed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error sending email: ' . $e->getMessage());
        }
    }
}
