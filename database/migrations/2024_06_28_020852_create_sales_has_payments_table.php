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
        Schema::create('sales_has_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('saleId')->nullable();
            $table->bigInteger('paymentTypeId')->nullable();
            $table->bigInteger('bankListId')->nullable();
            $table->string('cashInfo')->nullable();
            $table->string('checkInfo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_has_payments');
    }
};
