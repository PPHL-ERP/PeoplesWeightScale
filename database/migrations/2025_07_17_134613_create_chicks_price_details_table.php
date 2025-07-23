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
        Schema::create('chicks_price_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cpId')->nullable()->index();
            $table->bigInteger('productId')->nullable()->index();
            $table->decimal('qty', 8, 2)->nullable();
            $table->bigInteger('dailyPriceId')->nullable()->index();
            $table->double('dPrice')->nullable();
            $table->double('cPrice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chicks_price_details');
    }
};