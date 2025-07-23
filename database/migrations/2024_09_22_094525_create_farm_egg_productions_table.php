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
        Schema::create('farm_egg_productions', function (Blueprint $table) {
            $table->id();
            $table->string('productionId')->nullable();
            $table->bigInteger('sectorId')->nullable();
            $table->bigInteger('productId')->nullable();
            $table->bigInteger('flockId')->nullable();
            $table->double('flockTotal')->default(0);
            $table->date('date')->nullable();
            $table->decimal('qty', 10, 2)->nullable(); 
            $table->longText('note')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes for optimizing query performance
            $table->index('sectorId');   // Index on sectorId
            $table->index('productId');  // Index on productId
            $table->index('flockId');    // Index on flockId
            $table->index('date');       // Index on date
            $table->index('status');     // Index on status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_egg_productions');
    }

};
