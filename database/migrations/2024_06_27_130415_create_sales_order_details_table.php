<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_order_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('saleId')->nullable()->index();
            $table->bigInteger('productId')->nullable()->index();
            $table->bigInteger('unitId')->nullable();
            $table->double('tradePrice')->nullable();
            $table->double('salePrice')->nullable();
            $table->string('qty')->nullable();
            $table->string('unitBatchNo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_details');
    }
};