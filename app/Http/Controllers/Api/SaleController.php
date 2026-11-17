<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        return Sale::with(['customer:customer_id,customer_name', 'product:product_id,product_name,price'])
            ->orderByDesc('sale_date')->orderByDesc('sale_id')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,customer_id',
            'product_id' => 'required|exists:products,product_id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
        ]);
        return response()->json(Sale::create($data)->load(['customer','product']), 201);
    }

    public function show(Sale $sale)
    {
        return $sale->load(['customer','product']);
    }

    public function update(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,customer_id',
            'product_id' => 'sometimes|required|exists:products,product_id',
            'quantity' => 'sometimes|required|integer|min:1',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'sale_date' => 'sometimes|required|date',
        ]);
        $sale->update($data);
        return $sale->load(['customer','product']);
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();
        return response()->noContent();
    }
}
