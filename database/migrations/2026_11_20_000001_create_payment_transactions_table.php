<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_id')->unique();
            $table->string('order_number');
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PHP');
            $table->string('payment_method'); // gcash, credit_card, debit_card
            $table->string('status')->default('pending'); // pending, processing, successful, failed, expired, cancelled
            $table->string('provider')->default('paymongo');
            $table->string('provider_id')->nullable()->index();
            $table->string('provider_reference')->nullable();
            $table->text('checkout_url')->nullable();
            $table->json('provider_response')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
