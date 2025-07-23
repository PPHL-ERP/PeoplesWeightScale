<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('trId')->nullable();
            $table->string('transferHead')->nullable();
            $table->string('trType')->nullable();
            $table->bigInteger('fromStore')->nullable()->index();
            $table->bigInteger('toStore')->nullable()->index();
            $table->string(column: 'transportType')->nullable();
            $table->string(column: 'driverName')->nullable();
            $table->string('mobile')->nullable();
            $table->string(column: 'vehicleNo')->nullable();
            $table->date('date')->nullable()->index();
            $table->string('loadBy')->nullable();
            $table->string('labourGroupId')->nullable();
            $table->double('labourBill', 15, 2)->nullable();
            $table->longText('note')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_transfers');
    }
};