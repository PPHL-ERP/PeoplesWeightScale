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
        Schema::create('sales_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('saleId')->nullable();
            $table->bigInteger('bookingId')->nullable()->index();
            $table->bigInteger('saleCategoryId')->nullable()->index();
            $table->bigInteger('dealerId')->nullable();
            $table->bigInteger('salesPointId')->nullable()->index();
            $table->string('saleType')->nullable();
            $table->string('salesPerson')->nullable();
            $table->string('transportType')->nullable();
            $table->json('outTransportInfo')->nullable();
            $table->double('dueAmount')->nullable();
            $table->double('totalAmount')->nullable();
            $table->double('discount')->nullable();
            $table->string('discountType')->nullable();
            $table->double('fDiscount')->nullable();
            $table->double('vat')->nullable();
            $table->date('invoiceDate')->nullable()->index();
            $table->date('dueDate')->nullable();
            $table->longText('note')->nullable();
            $table->string('pOverRideBy')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_drafts');
    }
};