<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StripePaymentService
{
    public function create(array $data): array
    {
        $method = $data['payment_method'];
        $this->assertSupportedMethod($method);

        $referenceId = 'TB-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
        $transaction = PaymentTransaction::create([
            'reference_id' => $referenceId,
            'order_number' => $data['order_number'],
            'description' => $data['description'] ?? ('Payment for order ' . $data['order_number']),
            'amount' => $data['amount'],
            'currency' => 'PHP',
            'payment_method' => $method,
            'status' => 'pending',
            'provider' => 'stripe',
            'metadata' => $data['metadata'] ?? null,
        ]);

        return $method === 'gcash'
            ? $this->createGcashIntent($transaction, $data)
            : $this->createCardIntent($transaction, $data);
    }

    protected function createGcashIntent(PaymentTransaction $transaction, array $data): array
    {
        $amount = (int) round(((float) $transaction->amount) * 100); // centavos
        $payload = [
            'amount' => $amount,
            'currency' => strtolower(config('stripe.currency', 'php')),
            'payment_method_types[]' => 'gcash',
            'description' => $transaction->description,
            'confirm' => 'true',
            'payment_method_data[type]' => 'gcash',
            'payment_method_data[billing_details][name]' => $data['customer_name'] ?? 'Titas Bakery Customer',
            'return_url' => $data['success_redirect_url'] ?? config('stripe.success_redirect_url') ?? url('/payment/success'),
            'metadata[reference_id]' => $transaction->reference_id,
            'metadata[order_number]' => $transaction->order_number,
            'metadata[payment_method]' => $transaction->payment_method,
        ];

        $res = $this->stripePost('/v1/payment_intents', $payload);
        $body = $res->json();
        if (!$res->successful()) {
            $transaction->update(['status' => 'failed', 'provider_response' => $body]);
            return ['ok' => false, 'transaction' => $transaction->fresh(), 'error' => $body];
        }

        $status = $this->mapIntentStatus($body['status'] ?? 'requires_payment_method', $body);
        $checkoutUrl = $body['next_action']['redirect_to_url']['url'] ?? null;
        $transaction->update([
            'status' => $status,
            'provider_id' => $body['id'] ?? null,
            'provider_reference' => $transaction->reference_id,
            'checkout_url' => $checkoutUrl,
            'provider_response' => $body,
            'paid_at' => $status === 'successful' ? now() : null,
        ]);

        return [
            'ok' => in_array($status, ['pending', 'processing', 'successful'], true),
            'transaction' => $transaction->fresh(),
            'checkout_url' => $checkoutUrl,
            'client_secret' => $body['client_secret'] ?? null,
        ];
    }

    protected function createCardIntent(PaymentTransaction $transaction, array $data): array
    {
        // Secure flow: frontend creates PaymentMethod via Stripe.js and sends only payment_method_id.
        $paymentMethodId = $data['payment_method_id'] ?? null;
        if (!$paymentMethodId) {
            $transaction->update(['status' => 'failed', 'provider_response' => ['message' => 'Missing payment_method_id. Use Stripe.js secure card collection.']]);
            return ['ok' => false, 'transaction' => $transaction->fresh(), 'error' => ['message' => 'payment_method_id is required for card payments.']];
        }

        $amount = (int) round(((float) $transaction->amount) * 100);
        $payload = [
            'amount' => $amount,
            'currency' => strtolower(config('stripe.currency', 'php')),
            'payment_method_types[]' => 'card',
            'payment_method' => $paymentMethodId,
            'confirm' => 'true',
            'description' => $transaction->description,
            'return_url' => $data['success_redirect_url'] ?? config('stripe.success_redirect_url') ?? url('/payment/success'),
            'metadata[reference_id]' => $transaction->reference_id,
            'metadata[order_number]' => $transaction->order_number,
            'metadata[payment_method]' => $transaction->payment_method,
        ];

        $res = $this->stripePost('/v1/payment_intents', $payload);
        $body = $res->json();
        if (!$res->successful()) {
            $transaction->update(['status' => 'failed', 'provider_response' => $body]);
            return ['ok' => false, 'transaction' => $transaction->fresh(), 'error' => $body];
        }

        $status = $this->mapIntentStatus($body['status'] ?? 'requires_payment_method', $body);
        $actionUrl = $body['next_action']['redirect_to_url']['url'] ?? null;
        $transaction->update([
            'status' => $status,
            'provider_id' => $body['id'] ?? null,
            'provider_reference' => $transaction->reference_id,
            'checkout_url' => $actionUrl,
            'provider_response' => $body,
            'paid_at' => $status === 'successful' ? now() : null,
        ]);

        return [
            'ok' => in_array($status, ['pending', 'processing', 'successful'], true),
            'transaction' => $transaction->fresh(),
            'requires_action' => !empty($actionUrl) || ($body['status'] ?? '') === 'requires_action',
            'action_url' => $actionUrl,
            'client_secret' => $body['client_secret'] ?? null,
        ];
    }

    public function handleWebhook(string $rawPayload, array $headers): array
    {
        $verified = $this->verifyWebhookSignature($rawPayload, $headers);
        if (!$verified['ok']) return $verified;

        $event = json_decode($rawPayload, true) ?: [];
        $type = $event['type'] ?? null;
        $intent = $event['data']['object'] ?? [];
        $intentId = $intent['id'] ?? null;
        $reference = $intent['metadata']['reference_id'] ?? null;

        $transaction = $reference ? PaymentTransaction::where('reference_id', $reference)->first() : null;
        if (!$transaction && $intentId) $transaction = PaymentTransaction::where('provider_id', $intentId)->first();
        if (!$transaction) return ['ok' => true, 'message' => 'Transaction not found or ignored'];

        $newStatus = $this->mapIntentStatus($intent['status'] ?? $type, $intent);
        if ($transaction->status === 'successful' && $newStatus !== 'successful') {
            return ['ok' => true, 'message' => 'Ignored non-success update for successful transaction'];
        }
        if ($transaction->status === $newStatus && in_array($newStatus, ['successful','failed','expired','cancelled'], true)) {
            return ['ok' => true, 'message' => 'Duplicate webhook ignored'];
        }

        $transaction->update([
            'status' => $newStatus,
            'provider_id' => $intentId ?: $transaction->provider_id,
            'provider_response' => $event,
            'paid_at' => $newStatus === 'successful' ? ($transaction->paid_at ?? now()) : $transaction->paid_at,
        ]);

        return ['ok' => true, 'transaction' => $transaction->fresh()];
    }

    protected function verifyWebhookSignature(string $rawPayload, array $headers): array
    {
        $secret = config('stripe.webhook_secret');
        if (!$secret) return ['ok' => true, 'message' => 'Webhook secret not configured; skipping signature verification in sandbox setup.'];

        $sig = $headers['stripe-signature'][0] ?? $headers['Stripe-Signature'][0] ?? ($headers['stripe-signature'] ?? '');
        if (is_array($sig)) $sig = $sig[0] ?? '';
        if (!$sig) return ['ok' => false, 'code' => 401, 'message' => 'Missing Stripe-Signature header'];

        $parts = collect(explode(',', (string) $sig))->mapWithKeys(function ($p) {
            [$k, $v] = array_pad(explode('=', trim($p), 2), 2, null);
            return [$k => $v];
        });
        $t = $parts['t'] ?? null;
        $v1 = $parts['v1'] ?? null;
        if (!$t || !$v1) return ['ok' => false, 'code' => 401, 'message' => 'Invalid Stripe signature format'];

        $expected = hash_hmac('sha256', $t . '.' . $rawPayload, $secret);
        if (!hash_equals($expected, (string) $v1)) return ['ok' => false, 'code' => 401, 'message' => 'Invalid webhook signature'];
        return ['ok' => true];
    }

    protected function mapIntentStatus(?string $status, array $intent = []): string
    {
        return match (strtolower((string) $status)) {
            'succeeded' => 'successful',
            'processing', 'requires_action', 'requires_capture' => 'processing',
            'requires_payment_method', 'requires_confirmation' => 'pending',
            'canceled' => 'cancelled',
            default => isset($intent['last_payment_error']) ? 'failed' : 'pending',
        };
    }

    protected function assertSupportedMethod(string $method): void
    {
        if (!in_array($method, ['gcash', 'credit_card', 'debit_card'], true)) {
            abort(422, 'Unsupported payment method. Allowed: GCash, Credit Card, Debit Card.');
        }
    }

    protected function stripePost(string $path, array $form)
    {
        return Http::withToken(config('stripe.secret_key'))
            ->asForm()
            ->acceptJson()
            ->post('https://api.stripe.com' . $path, $form);
    }
}
