<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        foreach ($products as $index => $product) {
            $stock = 20 + (($index * 7) % 120);
            $reorder = 15 + (($index * 3) % 25);
            Inventory::create([
                'product_id' => $product->product_id,
                'stock_quantity' => $stock,
                'reorder_level' => $reorder,
                'restock_quantity' => $reorder + 30,
                'last_updated' => now()->subDays($index % 10),
            ]);
        }
    }
}
