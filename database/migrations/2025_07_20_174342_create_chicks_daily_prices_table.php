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
        Schema::create('chicks_daily_prices', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable()->index();
            $table->bigInteger('pId')->nullable()->index();
            $table->bigInteger('cZoneId')->nullable()->index();
            $table->decimal('pCost', 10, 2)->nullable();
            $table->decimal('mrp', 10, 2)->nullable();
            $table->decimal('salePrice', 10, 2)->nullable();
            $table->bigInteger('categoryId')->nullable();
            $table->bigInteger('subCategoryId')->nullable();
            $table->bigInteger('childCategoryId')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->string(column: 'status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chicks_daily_prices');
    }
};
