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
        Schema::create('feed_sales_return_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('saleReturnId')->nullable()->index();
            $table->bigInteger('productId')->nullable()->index();
            $table->bigInteger('unitId')->nullable()->index();
            $table->double('tradePrice')->nullable();
            $table->double('salePrice')->nullable();
            $table->string('qty')->nullable();
            $table->string('rQty')->nullable();
            $table->longText('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_sales_return_details');
    }
};
