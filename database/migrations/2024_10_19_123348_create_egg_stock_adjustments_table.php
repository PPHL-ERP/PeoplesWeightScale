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
        Schema::create('egg_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjId')->nullable();
            $table->bigInteger('sectorId')->nullable()->index();
            $table->bigInteger('productId')->nullable()->index();
            $table->date('date')->nullable()->index();
            $table->decimal('initialQty', 10, 2)->nullable();
            $table->decimal('adjQty', 10, 2)->nullable();
            $table->decimal('finalQty', 10, 2)->nullable();
            $table->string('adjType')->nullable();
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
        Schema::dropIfExists('egg_stock_adjustments');
    }
};
