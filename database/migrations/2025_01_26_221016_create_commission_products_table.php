<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commissionId')->nullable()->index();
            $table->unsignedBigInteger('productId')->nullable()->index();
            $table->double('generalCommissionPercentagePerBag', 15, 2)->default('0');
            $table->double('cashIncentivePerBag', 15, 2)->default('0');
            $table->double('monthlyTargetQuantity', 15, 2)->default('0');
            $table->double('monthlyTargetPerBagCashAmount', 15, 2)->default('0');
            $table->double('yearlyTargetQuantity', 15, 2)->default('0');
            $table->double('yearlyTargetPerBagCashAmount', 15, 2)->default('0');
            $table->double('perBagTransportDiscountAmount', 15, 2)->default('0');
            $table->double('specialTargetQuantity', 15, 2)->default('0');
            $table->double('specialTargetPerBagCashAmount', 15, 2)->default('0');
            $table->double('incentiveCashBack', 15, 2)->default('0');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_products');
    }
};
