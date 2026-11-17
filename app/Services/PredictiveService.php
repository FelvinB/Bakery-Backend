<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class PredictiveService
{
    public function forecast(): array
    {
        $products = Product::all();
        $productForecasts = [];

        foreach ($products as $product) {
            $recent = Sale::where('product_id', $product->product_id)
                ->select('sale_date', DB::raw('SUM(quantity) as qty'))
                ->groupBy('sale_date')
                ->orderByDesc('sale_date')
                ->take(3)
                ->pluck('qty');

            $values = $recent->values()->all();
            $avg = count($values) > 0 ? array_sum($values) / count($values) : 0;
            $predictedDaily = round($avg, 2);
            $predictedWeekly = round($avg * 7, 2);

            $inventory = Inventory::where('product_id', $product->product_id)->first();
            $stock = $inventory?->stock_quantity ?? 0;

            $productForecasts[] = [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'category' => $product->category,
                'price' => (float) $product->price,
                'recent_sales' => array_reverse($values),
                'predicted_daily_demand' => $predictedDaily,
                'predicted_weekly_demand' => $predictedWeekly,
                'current_stock' => (int) $stock,
                'potential_shortage' => $predictedDaily > $stock,
                'estimated_daily_revenue' => round($predictedDaily * (float) $product->price, 2),
            ];
        }

        usort($productForecasts, fn ($a, $b) => $b['predicted_daily_demand'] <=> $a['predicted_daily_demand']);

        $dailyTrend = Sale::select('sale_date', DB::raw('SUM(total_amount) as actual'))
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->take(14)
            ->get()
            ->map(function ($row) {
                return [
                    'sale_date' => $row->sale_date->format('Y-m-d'),
                    'actual' => (float) $row->actual,
                ];
            })->values()->all();

        $lastThree = array_slice($dailyTrend, -3);
        $base = count($lastThree) ? array_sum(array_column($lastThree, 'actual')) / count($lastThree) : 0;
        $predictions = [];
        $lastDate = count($dailyTrend) ? $dailyTrend[count($dailyTrend) - 1]['sale_date'] : now()->format('Y-m-d');
        for ($i = 1; $i <= 7; $i++) {
            $date = date('Y-m-d', strtotime($lastDate . " +{$i} day"));
            $factor = 1 + (($i % 3) - 1) * 0.03; // slight variation
            $predictions[] = [
                'sale_date' => $date,
                'predicted' => round($base * $factor, 2),
            ];
        }

        $totalFutureRevenue = round(array_sum(array_column($predictions, 'predicted')), 2);

        return [
            'product_forecasts' => $productForecasts,
            'daily_actual_vs_predicted' => $dailyTrend,
            'future_sales' => $predictions,
            'predicted_weekly_revenue' => $totalFutureRevenue,
            'potential_shortages' => array_values(array_filter($productForecasts, fn ($p) => $p['potential_shortage'])),
        ];
    }
}
