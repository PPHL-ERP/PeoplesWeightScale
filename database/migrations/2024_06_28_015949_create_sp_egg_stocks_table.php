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
        Schema::create('sp_egg_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('spId')->nullable();
            $table->bigInteger('stockId')->nullable();
            $table->bigInteger('farmStockId')->nullable();
            $table->date('stockDate')->nullable();
            $table->date('saleDate')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp_egg_stocks');
    }
};
