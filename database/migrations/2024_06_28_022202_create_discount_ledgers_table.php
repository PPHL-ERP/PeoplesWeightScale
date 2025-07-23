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
        Schema::create('discount_ledgers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('saleId')->nullable()->index();
            $table->bigInteger('saleCategoryId')->nullable()->index();
            $table->bigInteger('dealerId')->nullable()->index();
            $table->json('saleDetails')->nullable();
            $table->double('totalPrice')->nullable();
            $table->double('discountPrice')->nullable();
            $table->date('date')->nullable()->index();
            $table->bigInteger('appBy')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_ledgers');
    }
};
