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
        Schema::table('sectors', function (Blueprint $table) {
            $table->decimal('feedDepotCost', 12, 2)->after('salesPointName')->nullable();
            $table->decimal('chicksDepotCost', 12, 2)->after('feedDepotCost')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->dropColumn('feedDepotCost');
            $table->dropColumn('chicksDepotCost');
        });
    }
};