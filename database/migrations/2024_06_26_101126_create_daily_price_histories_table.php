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
        Schema::create('daily_price_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('dailyPriceId')->nullable();
            $table->bigInteger('productId')->nullable()->index();
            $table->bigInteger('categoryId')->nullable()->index();
            $table->bigInteger('availableQty')->nullable();
            $table->decimal('newPrice', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->date('date')->nullable()->index();
            $table->time('time')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_price_histories');
    }
};
