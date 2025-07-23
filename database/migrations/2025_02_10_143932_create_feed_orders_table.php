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
        Schema::create('feed_orders', function (Blueprint $table) {
            $table->id();
            $table->string('feedId')->nullable();
            $table->bigInteger('bookingId')->nullable()->index();
            $table->bigInteger('saleCategoryId')->nullable()->index();
            $table->bigInteger('dealerId')->nullable()->index();
            $table->bigInteger('salesPointId')->nullable()->index();
            $table->bigInteger('feedDraftId')->nullable()->index();
            $table->string('saleType')->nullable();
            $table->string('salesPerson')->nullable();
            $table->bigInteger('companyId')->nullable()->index();
            $table->bigInteger('chartOfHeadId')->nullable()->index();
            $table->bigInteger('commissionId')->nullable()->index();
            $table->string('transportType')->nullable();
            $table->json('outTransportInfo')->nullable();
            $table->double('dueAmount')->nullable();
            $table->double('totalAmount')->nullable();
            $table->double('discount')->nullable();
            $table->string('discountType')->nullable();
            $table->double('fDiscount')->nullable();
            $table->double('vat')->nullable();
            $table->date('invoiceDate')->nullable()->index();
            $table->date('dueDate')->nullable()->index();
            $table->longText('note')->nullable();
            $table->string('pOverRideBy')->nullable();
            $table->double('transportCost')->nullable();
            $table->json('othersCost')->nullable();
            $table->double('depotCost')->nullable();
            $table->string('billingAddress')->nullable();
            $table->string('deliveryAddress')->nullable();
            $table->string('paymentStatus')->nullable();
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
        Schema::dropIfExists('feed_orders');
    }
};