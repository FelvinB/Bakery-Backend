<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DescriptiveService
{
    public function summary(): array
    {
        $totalRevenue = (float) Sale::sum('total_amount');
        $totalProductsSold = (int) Sale::sum('quantity');
        $totalCustomers = (int) Customer::count();
        $lowStockItems = (int) Inventory::whereColumn('stock_quantity', '<=', 'reorder_level')->count();

        $bestSelling = Sale::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product:product_id,product_name,category')
            ->take(5)
            ->get();

        $lowestSelling = Sale::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('product_id')
            ->orderBy('total_sold')
            ->with('product:product_id,product_name,category')
            ->take(5)
            ->get();

        $dailySales = Sale::select('sale_date', DB::raw('SUM(total_amount) as total'), DB::raw('SUM(quantity) as qty'))
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->take(30)
            ->get();

        $monthlySales = Sale::select(DB::raw("DATE_FORMAT(sale_date, '%Y-%m') as month"), DB::raw('SUM(total_amount) as total'), DB::raw('SUM(quantity) as qty'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $categorySales = Sale::join('products', 'sales.product_id', '=', 'products.product_id')
            ->select('products.category', DB::raw('SUM(sales.total_amount) as total'), DB::raw('SUM(sales.quantity) as qty'))
            ->groupBy('products.category')
            ->get();

        $inventoryStatus = Inventory::with('product:product_id,product_name,category')
            ->select('inventory_id', 'product_id', 'stock_quantity', 'reorder_level', 'restock_quantity', 'last_updated')
            ->orderBy('stock_quantity')
            ->get();

        return [
            'kpis' => [
                'total_revenue' => $totalRevenue,
                'total_products_sold' => $totalProductsSold,
                'total_customers' => $totalCustomers,
                'total_products' => (int) Product::count(),
                'low_stock_items' => $lowStockItems,
            ],
            'best_selling' => $bestSelling,
            'lowest_selling' => $lowestSelling,
            'daily_sales' => $dailySales,
            'monthly_sales' => $monthlySales,
            'category_sales' => $categorySales,
            'inventory_status' => $inventoryStatus,
        ];
    }
}
