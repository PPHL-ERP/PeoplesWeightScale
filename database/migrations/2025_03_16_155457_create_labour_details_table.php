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
        Schema::create('labour_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('labourId')->nullable()->index();
            $table->bigInteger('depotId')->nullable()->index();
            $table->bigInteger('unitId')->nullable()->index();
            $table->string('transactionId')->nullable();
            $table->string('transactionType')->nullable();
            $table->string('workType')->nullable();
            $table->date('tDate')->nullable()->index();
            $table->double('qty')->nullable();
            $table->double('bAmount')->nullable();
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
        Schema::dropIfExists('labour_details');
    }
};