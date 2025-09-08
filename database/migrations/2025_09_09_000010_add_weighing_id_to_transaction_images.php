<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaction_images', function (Blueprint $table) {
            $table->unsignedBigInteger('weighing_id')->nullable()->after('id')->index();
            // add foreign key if WeightTransaction exists; we'll not enforce cascade to be safe
            // if weight_transactions table doesn't exist this will be ignored by developer
            if (Schema::hasTable('weight_transactions')) {
                $table->foreign('weighing_id')->references('id')->on('weight_transactions')->onDelete('set null');
            }

            // unique per weighing + checksum to dedupe per weighing
            $table->unique(['weighing_id', 'checksum_sha256'], 'weighing_checksum_unique');
        });
    }

    public function down()
    {
        Schema::table('transaction_images', function (Blueprint $table) {
            $table->dropUnique('weighing_checksum_unique');
            if (Schema::hasTable('weight_transactions')) {
                $table->dropForeign(['weighing_id']);
            }
            $table->dropColumn('weighing_id');
        });
    }
};
