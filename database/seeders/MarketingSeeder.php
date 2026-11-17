<?php

namespace Database\Seeders;

use App\Models\Marketing;
use Illuminate\Database\Seeder;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Discount', 'Bundle Promo', 'Seasonal Sale', 'Loyalty Reward', 'Freebie'];
        for ($i = 1; $i <= 50; $i++) {
            Marketing::create([
                'campaign_name' => "Campaign {$i}",
                'promotion_type' => $types[$i % count($types)],
                'discount_percentage' => ($i % 5) * 5,
                'campaign_date' => now()->subDays(100 - ($i * 2))->format('Y-m-d'),
                'sales_generated' => 500 + ($i * 37),
            ]);
        }
    }
}
