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
        Schema::create('labour_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('labourId')->nullable()->index();
            $table->date('billStartDate')->nullable()->index();
            $table->date('billEndDate')->nullable()->index();
            $table->date('paymentDate')->nullable()->index();
            $table->double('totalQty')->nullable();
            $table->double('totalAmount')->nullable();
            $table->string('priceInfo')->nullable();
            $table->longText('note')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->string('billStatus')->nullable();
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
        Schema::dropIfExists('labour_payments');
    }
};