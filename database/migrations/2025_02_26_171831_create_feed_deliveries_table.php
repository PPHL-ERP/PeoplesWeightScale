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
        Schema::create('feed_deliveries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('feedId')->nullable()->index();
            $table->bigInteger('dealerId')->nullable()->index();
            $table->string('salesPerson')->nullable();
            $table->longText('deliveryPointDetails')->nullable();
            $table->json('deliveryPersonDetails')->nullable();
            $table->date('deliveryDate')->nullable()->index();
            $table->string('transportType')->nullable();
            $table->string('roadInfo')->nullable();
            $table->string(column: 'driverName')->nullable();
            $table->string('mobile')->nullable();
            $table->string(column: 'vehicleNo')->nullable();
            $table->longText('note')->nullable();
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
        Schema::dropIfExists('feed_deliveries');
    }
};
