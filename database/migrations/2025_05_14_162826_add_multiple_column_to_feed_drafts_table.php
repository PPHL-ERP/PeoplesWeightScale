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
        Schema::table('feed_drafts', function (Blueprint $table) {
            $table->bigInteger('loadBy' )->after('transportType')->nullable();
            $table->string('transportBy')->after('loadBy')->nullable();
            $table->bigInteger('subCategoryId' )->after('saleCategoryId')->nullable()->index();
            $table->bigInteger('childCategoryId' )->after('subCategoryId')->nullable()->index();
            $table->double('subTotal')->after('outTransportInfo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_drafts', function (Blueprint $table) {
            $table->dropColumn('loadBy');
            $table->dropColumn('transportBy');
            $table->dropColumn('subCategoryId');
            $table->dropColumn('childCategoryId');
            $table->dropColumn('subTotal');
        });
    }
};
