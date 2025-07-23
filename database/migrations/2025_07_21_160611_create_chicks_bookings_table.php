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
        Schema::create('chicks_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('cBookingId')->nullable();
            $table->bigInteger('dealerId')->nullable()->index();
            $table->bigInteger('categoryId')->nullable();
            $table->bigInteger('subCategoryId')->nullable();
            $table->bigInteger('childCategoryId')->nullable();
            $table->bigInteger('commissionId')->nullable()->index();
            $table->bigInteger('bookingPointId')->nullable()->index();
            $table->bigInteger('chicksPriceId')->nullable();
            $table->bigInteger('bookingPerson')->nullable()->index();
            $table->string('bookingType')->nullable();
            $table->string('isBookingMoney')->nullable();
            $table->string('isMultiDelivery')->nullable();
            $table->json('deliveryDetails')->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('discountType')->nullable();
            $table->decimal('advanceAmount', 10, 2)->default(0);
            $table->decimal('totalAmount', 10, 2)->default(0);
            $table->date('bookingDate')->nullable()->index();
            $table->date('invoiceDate')->nullable()->index();
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
        Schema::dropIfExists('chicks_bookings');
    }
};