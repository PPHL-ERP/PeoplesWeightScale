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
        Schema::create('daily_prices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('productId')->nullable()->index();
            $table->bigInteger('categoryId')->nullable()->index();
            $table->bigInteger('availableQty')->nullable();
            $table->decimal('oldPrice', 10, 2)->nullable();
            $table->decimal('currentPrice', 10, 2)->nullable();
            $table->date('date')->nullable()->index();
            $table->longText('note')->nullable();
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
        Schema::dropIfExists('daily_prices');
    }
};