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
        Schema::table('egg_stocks', function (Blueprint $table) {
            // Add foreign key constraints
            $table->foreign('sectorId')
                ->references('id')
                ->on('sectors')
                ->onDelete('set null'); // Set to null if the referenced record is deleted

            $table->foreign('productId')
                ->references('id')
                ->on('products')
                ->onDelete('set null'); // Set to null if the referenced record is deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_stocks', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['sectorId']);
            $table->dropForeign(['productId']);
        });
    }
};
