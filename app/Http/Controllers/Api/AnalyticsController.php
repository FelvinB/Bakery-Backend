<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DescriptiveService;
use App\Services\PredictiveService;
use App\Services\PrescriptiveService;

class AnalyticsController extends Controller
{
    public function __construct(
        private DescriptiveService $descriptive,
        private PredictiveService $predictive,
        private PrescriptiveService $prescriptive,
    ) {}

    public function dashboard()
    {
        $desc = $this->descriptive->summary();
        $pred = $this->predictive->forecast();
        $pres = $this->prescriptive->recommendations();

        return response()->json([
            'kpis' => $desc['kpis'],
            'best_selling' => $desc['best_selling']->take(5)->values(),
            'daily_sales' => $desc['daily_sales'],
            'category_sales' => $desc['category_sales'],
            'future_sales' => $pred['future_sales'],
            'predicted_weekly_revenue' => $pred['predicted_weekly_revenue'],
            'shortage_warnings' => $pres['shortage_warnings'],
            'production_recommendations' => array_slice($pres['production_recommendations'], 0, 5),
        ]);
    }

    public function descriptive()
    {
        return response()->json($this->descriptive->summary());
    }

    public function predictive()
    {
        return response()->json($this->predictive->forecast());
    }

    public function prescriptive()
    {
        return response()->json($this->prescriptive->recommendations());
    }
}
