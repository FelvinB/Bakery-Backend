<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'reference_id',
        'order_number',
        'description',
        'amount',
        'currency',
        'payment_method',
        'status',
        'provider',
        'provider_id',
        'provider_reference',
        'checkout_url',
        'provider_response',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];
}
