<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing', function (Blueprint $table) {
            $table->id('marketing_id');
            $table->string('campaign_name');
            $table->string('promotion_type');
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->date('campaign_date');
            $table->decimal('sales_generated', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing');
    }
};
