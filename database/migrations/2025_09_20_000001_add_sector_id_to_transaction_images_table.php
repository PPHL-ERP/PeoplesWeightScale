<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaction_images', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_images', 'sector_id')) {
                $table->unsignedBigInteger('sector_id')->nullable()->after('transaction_id')->index();
            }
        });
    }

    public function down()
    {
        Schema::table('transaction_images', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_images', 'sector_id')) {
                $table->dropColumn('sector_id');
            }
        });
    }
};
