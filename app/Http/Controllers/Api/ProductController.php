<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with('inventory')->orderByDesc('product_id')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        return response()->json(Product::create($data), 201);
    }

    public function show(Product $product)
    {
        return $product->load('inventory');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'product_name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        $product->update($data);
        return $product->load('inventory');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }
}
