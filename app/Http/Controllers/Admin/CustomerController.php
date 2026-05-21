<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\RespondsWithDataTables;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    use RespondsWithDataTables;

    public function index()
    {
        return view('admin.customers.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Customer::query()->orderBy('id', 'desc');
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('institution_id', auth()->user()->institution_id);
        }

        return $this->dataTablesJson(
            $request,
            $query,
            ['name', 'email', 'phone'],
            function (Customer $row) {
                $statusHtml = $row->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                $actionHtml = '<button class="btn btn-sm btn-outline-primary edit-btn" data-id="' . $row->id . '" data-row=\'' . json_encode($row) . '\' title="Edit"><i class="fa fa-edit"></i></button>
                               <button class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $row->id . '" title="Delete"><i class="fa fa-trash"></i></button>';
                
                return [
                    'id' => $row->id,
                    'DT_RowIndex' => $row->id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'phone' => $row->phone,
                    'status' => $statusHtml,
                    'action' => $actionHtml,
                ];
            }
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['institution_id'] = auth()->user()->institution_id;
        $data['is_active'] = $request->has('is_active');

        Customer::create($data);

        return response()->json(['success' => true, 'message' => 'Customer added successfully.']);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $customer->update($data);

        return response()->json(['success' => true, 'message' => 'Customer updated successfully.']);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['success' => true, 'message' => 'Customer deleted successfully.']);
    }
}
