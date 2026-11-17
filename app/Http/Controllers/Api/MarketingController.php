<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function index()
    {
        return Marketing::orderByDesc('campaign_date')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'campaign_name' => 'required|string|max:255',
            'promotion_type' => 'required|string|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'campaign_date' => 'required|date',
            'sales_generated' => 'nullable|numeric|min:0',
        ]);
        return response()->json(Marketing::create($data), 201);
    }

    public function show(Marketing $marketing)
    {
        return $marketing;
    }

    public function update(Request $request, Marketing $marketing)
    {
        $data = $request->validate([
            'campaign_name' => 'sometimes|required|string|max:255',
            'promotion_type' => 'sometimes|required|string|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'campaign_date' => 'sometimes|required|date',
            'sales_generated' => 'nullable|numeric|min:0',
        ]);
        $marketing->update($data);
        return $marketing;
    }

    public function destroy(Marketing $marketing)
    {
        $marketing->delete();
        return response()->noContent();
    }
}
