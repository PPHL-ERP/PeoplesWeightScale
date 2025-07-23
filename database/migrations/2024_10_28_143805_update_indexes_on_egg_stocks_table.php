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
            // Add new indexes or modify existing ones
            $table->index(['sectorId', 'productId']); // Add a combined index on sectorId and productId
          
            // If you need to drop an index first, uncomment this line
            // $table->dropIndex(['old_index_name']); // Drop old index if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_stocks', function (Blueprint $table) {
            // Drop the indexes you added in the `up` method
            $table->dropIndex(['sectorId', 'productId']);
            $table->dropIndex(['trDate']);
        });
    }
};
