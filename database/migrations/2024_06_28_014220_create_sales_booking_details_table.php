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
        Schema::create('sales_booking_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bookingId')->nullable()->index();
            $table->bigInteger('productId')->nullable()->index();
            $table->bigInteger('unitId')->nullable();
            $table->string('bookingQty')->nullable();
            $table->longText('noteDetails')->nullable();
            $table->double('bookingPrice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_booking_details');
    }
};
