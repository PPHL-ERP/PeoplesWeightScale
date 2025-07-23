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
        Schema::create('chicks_stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sectorId')->nullable()->index();
            $table->bigInteger('productId')->nullable()->index();
            $table->bigInteger('breedId')->nullable()->index();
            $table->string('stockType')->nullable();
            $table->string('batchNo')->nullable();
            $table->date('stockDate')->nullable()->index();
            $table->decimal('approxQty', 8, 2)->nullable();
            $table->decimal('finalQty', 8, 2)->nullable();
            $table->decimal('closing', 10, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chicks_stocks');
    }
};
