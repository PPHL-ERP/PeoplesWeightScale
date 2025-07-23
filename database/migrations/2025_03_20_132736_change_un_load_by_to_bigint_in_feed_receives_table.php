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
        Schema::table('feed_receives', function (Blueprint $table) {
            DB::statement('ALTER TABLE feed_receives ALTER COLUMN "unLoadBy" TYPE BIGINT USING "unLoadBy"::bigint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_receives', function (Blueprint $table) {
            DB::statement('ALTER TABLE feed_receives ALTER COLUMN "unLoadBy" TYPE VARCHAR USING "unLoadBy"::VARCHAR');
        });
    }
};