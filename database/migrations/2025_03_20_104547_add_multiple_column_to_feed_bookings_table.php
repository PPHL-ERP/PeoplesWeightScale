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
        Schema::table('feed_bookings', function (Blueprint $table) {
            $table->bigInteger('subCategoryId' )->after('saleCategoryId')->nullable()->index();
            $table->bigInteger('childCategoryId' )->after('subCategoryId')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_bookings', callback: function (Blueprint $table) {
            $table->dropColumn('subCategoryId');
            $table->dropColumn('childCategoryId');
        });
    }
};
