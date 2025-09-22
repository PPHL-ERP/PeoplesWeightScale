<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('transaction_images', 'mode')) {
            Schema::table('transaction_images', function (Blueprint $table) {
                $table->string('mode')->nullable()->after('sector_id')->comment('capture mode, e.g., tare/gross');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('transaction_images', 'mode')) {
            Schema::table('transaction_images', function (Blueprint $table) {
                $table->dropColumn('mode');
            });
        }
    }
};
