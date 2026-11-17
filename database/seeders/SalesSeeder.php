<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $customerIds = Customer::pluck('customer_id')->all();
        $products = Product::all();
        $start = now()->subDays(60);
        for ($i = 0; $i < 80; $i++) {
            $product = $products[$i % $products->count()];
            $qty = 1 + ($i % 12);
            $date = (clone $start)->addDays($i % 60);
            Sale::create([
                'customer_id' => $customerIds[$i % count($customerIds)],
                'product_id' => $product->product_id,
                'quantity' => $qty,
                'unit_price' => $product->price,
                'total_amount' => $qty * $product->price,
                'sale_date' => $date->format('Y-m-d'),
            ]);
        }
    }
}
