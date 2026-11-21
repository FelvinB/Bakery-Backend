<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;

class StripePaymentController extends Controller
{
    public function __construct(private StripePaymentService $stripe) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_number' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|in:PHP',
            'payment_method' => 'required|in:gcash,credit_card,debit_card',
            'payment_method_id' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'success_redirect_url' => 'nullable|url',
            'failed_redirect_url' => 'nullable|url',
            'metadata' => 'nullable|array',
        ]);

        $result = $this->stripe->create($data);
        $status = ($result['ok'] ?? false) ? 201 : 422;
        return response()->json($result, $status);
    }

    public function show(string $reference)
    {
        $tx = PaymentTransaction::where('reference_id', $reference)->firstOrFail();
        return response()->json(['transaction' => $tx]);
    }

    public function webhook(Request $request)
    {
        $result = $this->stripe->handleWebhook($request->getContent(), $request->headers->all());
        $code = $result['code'] ?? 200;
        unset($result['code']);
        return response()->json($result, $code);
    }
}
