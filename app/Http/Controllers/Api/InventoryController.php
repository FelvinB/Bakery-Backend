<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        return Inventory::with('product:product_id,product_name,category,price')->orderByDesc('inventory_id')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'restock_quantity' => 'required|integer|min:0',
            'last_updated' => 'nullable|date',
        ]);
        return response()->json(Inventory::create($data)->load('product'), 201);
    }

    public function show(Inventory $inventory)
    {
        return $inventory->load('product');
    }

    public function update(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'product_id' => 'sometimes|required|exists:products,product_id',
            'stock_quantity' => 'sometimes|required|integer|min:0',
            'reorder_level' => 'sometimes|required|integer|min:0',
            'restock_quantity' => 'sometimes|required|integer|min:0',
            'last_updated' => 'nullable|date',
        ]);
        $inventory->update($data);
        return $inventory->load('product');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return response()->noContent();
    }
}
