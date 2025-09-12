<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendorNameToWeightTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weight_transactions', function (Blueprint $table) {
            $table->string('vendor_name')->nullable()->after('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('weight_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('weight_transactions', 'vendor_name')) {
                $table->dropColumn('vendor_name');
            }
        });
    }
}
