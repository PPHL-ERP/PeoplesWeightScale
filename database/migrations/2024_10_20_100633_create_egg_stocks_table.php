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
        Schema::create('egg_stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sectorId')->nullable()->index();
            $table->bigInteger('productId')->nullable()->index();
            $table->decimal('closing', 10, 2)->nullable();
            $table->date('trDate')->nullable()->index();
            $table->softDeletes();
            //$table->timestamps();
            $table->timestamp('created_at', 3)->nullable();
            $table->timestamp('updated_at', 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egg_stocks');
    }
};
