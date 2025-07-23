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
        Schema::create('bank_ledgers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('companyId')->nullable()->index();
            $table->bigInteger('sectorId')->nullable()->index();
            $table->string('trId')->nullable();
            $table->bigInteger('bankId')->nullable()->index();
            $table->string('trType')->nullable();
            $table->date('trDate')->nullable()->index();
            $table->double('companyBalance', 15, 2)->nullable();
            $table->double('sectorBalance', 15, 2)->nullable();
            $table->double('amount', 15, 2)->nullable();
            $table->double('balance', 15, 2)->nullable();
            $table->string('particular')->nullable();
            $table->longText('note')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_ledgers');
    }
};