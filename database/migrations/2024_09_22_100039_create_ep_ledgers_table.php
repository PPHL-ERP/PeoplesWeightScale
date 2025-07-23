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
        Schema::create('ep_ledgers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sectorId')->nullable();
            $table->bigInteger('productId')->nullable();
            $table->bigInteger('transactionId')->nullable();
            $table->string('trType')->nullable();
            $table->date('date')->nullable();
            $table->string('qty')->nullable();
            $table->double('lockQty')->default(0);
            $table->double('sectorBalance')->default(0);
            $table->double('closingBalance')->default(0);
            $table->longText('remarks')->nullable();
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
        Schema::dropIfExists('ep_ledgers');
    }
};
