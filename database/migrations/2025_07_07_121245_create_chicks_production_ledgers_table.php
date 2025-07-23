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
        Schema::create('chicks_production_ledgers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('hatcheryId')->nullable()->index();
            $table->bigInteger('productId')->nullable()->index();
            $table->string('transactionId')->nullable();
            $table->string('settingId')->nullable();
            $table->string('trType')->nullable();
            $table->date('date')->nullable()->index();
            $table->decimal('approxQty', 8, 2)->nullable();
            $table->decimal('finalQty', 8, 2)->nullable();
            $table->string('batchNo')->nullable();
            $table->longText('remarks')->nullable();
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
        Schema::dropIfExists('chicks_production_ledgers');
    }
};