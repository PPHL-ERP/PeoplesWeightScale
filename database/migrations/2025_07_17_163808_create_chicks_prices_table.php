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
        Schema::create('chicks_prices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('dealerId')->nullable()->index();
            $table->bigInteger('empId')->nullable()->index();
            $table->string('outDealerName')->nullable();
            $table->string('phone')->nullable();
            $table->date('date')->nullable()->index();
            $table->date('validityDate')->nullable()->index();
            $table->longText('note')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->string(column: 'status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chicks_prices');
    }
};