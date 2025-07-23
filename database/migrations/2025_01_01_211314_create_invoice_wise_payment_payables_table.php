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
        Schema::create('invoice_wise_payment_payables', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('paymentPayableId')->index();
            $table->bigInteger('purchaseInvoiceId')->index();
            $table->date('paidDate');
            $table->double('dueAmount');
            $table->double('paidAmount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_wise_payment_payables');
    }
};
