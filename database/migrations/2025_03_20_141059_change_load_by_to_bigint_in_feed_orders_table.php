<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('feed_orders', function (Blueprint $table) {
            DB::statement('ALTER TABLE feed_orders ALTER COLUMN "loadBy" TYPE BIGINT USING "loadBy"::bigint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_orders', function (Blueprint $table) {
            DB::statement('ALTER TABLE feed_orders ALTER COLUMN "loadBy" TYPE VARCHAR USING "loadBy"::VARCHAR');
        });
    }
};