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
        Schema::create('bank_transfer_infos', function (Blueprint $table) {
            $table->id();
            $table->string('btrId')->nullable();
            $table->bigInteger('companyId')->nullable()->index();
            $table->bigInteger('sectorId')->nullable()->index();
            $table->bigInteger('headId')->nullable()->index();
            $table->string('transactionId')->nullable();
            $table->bigInteger('bankIdFrom')->nullable();
            $table->bigInteger('bankIdTo')->nullable()->index();
            $table->date('transactionDate')->nullable()->index();
            $table->string('transferType')->nullable();
            $table->string('trPurpose')->nullable();
            $table->string('modeOfTransfer')->nullable();
            $table->double('amount', 15, 2)->nullable();
            $table->string('chequeNo')->nullable();
            $table->date('chequeDate')->nullable();
            $table->longText('note')->nullable();
            $table->string('status')->nullable();
            $table->bigInteger('entryBy')->nullable();
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
        Schema::dropIfExists('bank_transfer_infos');
    }
};