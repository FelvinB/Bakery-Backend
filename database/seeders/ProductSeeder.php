<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['Pandesal', 'Bread', 5, 2], ['Spanish Bread', 'Bread', 12, 6], ['Ensaymada', 'Pastry', 25, 12],
            ['Monay', 'Bread', 10, 4], ['Pan de Coco', 'Bread', 15, 7], ['Cheese Roll', 'Pastry', 20, 9],
            ['Ube Loaf', 'Cake', 85, 45], ['Chocolate Cake Slice', 'Cake', 45, 22], ['Mocha Cake Slice', 'Cake', 45, 22],
            ['Banana Bread', 'Bread', 60, 30], ['Butter Cookies', 'Cookies', 35, 15], ['Oatmeal Cookies', 'Cookies', 30, 14],
            ['Brownies', 'Pastry', 28, 13], ['Egg Pie Slice', 'Pastry', 30, 14], ['Buko Pie Slice', 'Pastry', 35, 16],
            ['Tasty Bread', 'Bread', 55, 28], ['Whole Wheat Loaf', 'Bread', 70, 35], ['Garlic Bread', 'Bread', 40, 18],
            ['Cinnamon Roll', 'Pastry', 32, 15], ['Mamon', 'Pastry', 22, 10],
        ];
        $count = 0;
        for ($round = 1; $count < 50; $round++) {
            foreach ($items as $i => $item) {
                $count++;
                if ($count > 50) break;
                $suffix = $round > 1 ? " {$round}" : '';
                Product::create([
                    'product_name' => $item[0] . $suffix,
                    'category' => $item[1],
                    'price' => $item[2] + ($round - 1),
                    'cost' => $item[3],
                    'description' => $item[0] . ' - freshly baked item.',
                ]);
            }
        }
    }
}
