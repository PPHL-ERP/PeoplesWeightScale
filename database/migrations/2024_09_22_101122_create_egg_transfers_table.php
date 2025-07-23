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
        Schema::create('egg_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('trId')->nullable();
            $table->string('transferHead')->nullable();
            $table->string('trType')->nullable();
            $table->bigInteger('fromStore')->nullable();
            $table->bigInteger('toStore')->nullable()->index();
            $table->string(column: 'transportType')->nullable();
            $table->string(column: 'driverName')->nullable();
            $table->string('mobile')->nullable();
            $table->string(column: 'vehicleNo')->nullable();
            $table->date('date')->nullable()->index();
            $table->string('loadBy')->nullable();
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
        Schema::dropIfExists('egg_transfers');
    }
};
