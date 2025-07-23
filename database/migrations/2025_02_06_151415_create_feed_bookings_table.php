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
        Schema::create('feed_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('bookingId')->nullable();
            $table->bigInteger('dealerId')->nullable()->index();
            $table->bigInteger('saleCategoryId')->nullable()->index();
            $table->bigInteger('bookingPointId')->nullable()->index();
            $table->bigInteger('bookingPerson')->nullable()->index();
            $table->bigInteger(column: 'commissionId')->nullable()->index();
            $table->string('bookingType')->nullable();
            $table->string('isBookingMoney')->nullable();
            $table->double('discount')->nullable();
            $table->string('discountType')->nullable();
            $table->double('advanceAmount')->nullable();
            $table->double('totalAmount')->nullable();
            $table->date('bookingDate')->nullable()->index();
            $table->date('invoiceDate')->nullable();
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
        Schema::dropIfExists('feed_bookings');
    }
};
