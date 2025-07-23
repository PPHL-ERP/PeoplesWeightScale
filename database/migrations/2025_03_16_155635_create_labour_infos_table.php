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
        Schema::create('labour_infos', function (Blueprint $table) {
            $table->id();
            $table->string('labourName')->nullable();
            $table->string('concernPerson')->nullable();
            $table->string('contactNo')->nullable();
            $table->string('location')->nullable();
            $table->bigInteger('depotId')->nullable()->index();
            $table->bigInteger(column: 'unitId')->nullable()->index();
            $table->date('contactDate')->nullable()->index();
            $table->date('expDate')->nullable()->index();
            $table->double('fPrice')->nullable();
            $table->double('cPrice')->nullable();
            $table->double('oPrice')->nullable();
            $table->string('paymentCycle')->nullable();
            $table->string('paymentType')->nullable();
            $table->string('paymentInfo')->nullable();
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
        Schema::dropIfExists('labour_infos');
    }
};