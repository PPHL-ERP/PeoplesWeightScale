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
        Schema::create('chicks_daily_price_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chicksDPriceId')->nullable();
            $table->date('date')->nullable()->index();
            $table->string('changeType')->nullable()->index();
            $table->bigInteger('pId')->nullable()->index();
            $table->bigInteger('cZoneId')->nullable()->index();
            $table->decimal('pCost', 10, 2)->nullable();
            $table->decimal('mrp', 10, 2)->nullable();
            $table->decimal('salePrice', 10, 2)->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chicks_daily_price_histories');
    }
};
