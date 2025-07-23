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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->double('depotCost', 15, 2)->nullable()->after('othersCost');
            $table->bigInteger('chartOfHeadId')->nullable()->after('depotCost');
            $table->string('paymentStatus')->nullable()->after('chartOfHeadId');
            $table->string('billingAddress')->nullable()->after('paymentStatus');
            $table->string('deliveryAddress')->nullable()->after('billingAddress');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('depotCost');
            $table->dropColumn('chartOfHeadId');
            $table->dropColumn('paymentStatus');
            $table->dropColumn('billingAddress');
            $table->dropColumn('deliveryAddress');
        });
    }
};
