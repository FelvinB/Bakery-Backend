<?php

namespace App\Services;

class PrescriptiveService
{
    public function __construct(
        private DescriptiveService $descriptive,
        private PredictiveService $predictive,
    ) {}

    public function recommendations(): array
    {
        $desc = $this->descriptive->summary();
        $pred = $this->predictive->forecast();

        $production = [];
        $restocking = [];
        $lowSellingAlerts = [];
        $shortageWarnings = [];
        $promotions = [];

        foreach ($pred['product_forecasts'] as $item) {
            $needed = (int) ceil(max(0, $item['predicted_daily_demand'] - $item['current_stock']));
            if ($needed > 0) {
                $production[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'current_stock' => $item['current_stock'],
                    'predicted_daily_demand' => $item['predicted_daily_demand'],
                    'recommended_additional_production' => $needed,
                    'reason' => 'Predicted demand exceeds current stock.',
                ];
                $restocking[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'current_stock' => $item['current_stock'],
                    'recommended_restock_quantity' => max($needed, (int) ceil($item['predicted_weekly_demand'])),
                    'reason' => 'Restock to cover predicted weekly demand.',
                ];
                $shortageWarnings[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'message' => "{$item['product_name']} may run out of stock soon.",
                ];
            } elseif ($item['current_stock'] > 0 && $item['predicted_daily_demand'] < ($item['current_stock'] * 0.2)) {
                $production[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'current_stock' => $item['current_stock'],
                    'predicted_daily_demand' => $item['predicted_daily_demand'],
                    'recommended_additional_production' => 0,
                    'reason' => 'Reduce production; stock is sufficient relative to demand.',
                ];
            }
        }

        foreach ($desc['lowest_selling'] as $row) {
            $name = $row->product->product_name ?? 'Unknown';
            $lowSellingAlerts[] = [
                'product_id' => $row->product_id,
                'product_name' => $name,
                'total_sold' => (int) $row->total_sold,
                'suggestion' => "Consider a discount or bundle promo for {$name} to improve sales.",
            ];
            $promotions[] = [
                'type' => 'Discount / Bundle',
                'target' => $name,
                'suggestion' => "Offer 10-15% off or pair {$name} with a best-selling item on slower days.",
            ];
        }

        if (empty($promotions)) {
            $promotions[] = [
                'type' => 'General Promotion',
                'target' => 'All products',
                'suggestion' => 'Schedule promotions during lower-sales days to boost traffic.',
            ];
        }

        return [
            'production_recommendations' => $production,
            'restocking_recommendations' => $restocking,
            'low_selling_alerts' => $lowSellingAlerts,
            'shortage_warnings' => $shortageWarnings,
            'promotional_suggestions' => $promotions,
            'insights' => [
                'best_seller_focus' => 'Increase production of best-selling products to maximize revenue.',
                'waste_reduction' => 'Align baking quantities with predicted daily demand to reduce leftovers.',
            ],
        ];
    }
}
