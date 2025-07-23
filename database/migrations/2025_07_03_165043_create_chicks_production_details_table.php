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
        Schema::create('chicks_production_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pId')->nullable()->index();
            $table->bigInteger('productId')->nullable()->index();
            $table->string('settingId')->nullable();
            $table->bigInteger('breedId')->nullable();
            $table->string('chicksType')->nullable();
            $table->string('batchNo')->nullable();
            $table->string('grade')->nullable();
            $table->decimal('approxQty', 8, 2)->nullable();
            $table->decimal('finalQty', 8, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chicks_production_details');
    }
};