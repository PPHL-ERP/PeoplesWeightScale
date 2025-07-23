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
        Schema::create('chicks_booking_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cbId')->nullable();
            $table->bigInteger('pId')->nullable()->index();
            $table->bigInteger('cdPriceId')->nullable()->index();
            $table->bigInteger('unitId')->nullable();
            $table->json('settingId')->nullable();
            $table->json('flockId')->nullable();
            $table->decimal('bQty', 8, 2)->nullable();
            $table->decimal('salePrice', 10, 2)->nullable();
            $table->decimal('mrp', 10, 2)->nullable();
            $table->longText('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chicks_booking_details');
    }
};