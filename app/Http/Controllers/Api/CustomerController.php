<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::orderByDesc('customer_id')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_type' => 'required|string|max:100',
            'contact_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);
        return response()->json(Customer::create($data), 201);
    }

    public function show(Customer $customer)
    {
        return $customer;
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'customer_name' => 'sometimes|required|string|max:255',
            'customer_type' => 'sometimes|required|string|max:100',
            'contact_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);
        $customer->update($data);
        return $customer;
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->noContent();
    }
}
