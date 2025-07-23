<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_wise_payment_receives', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->string('note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_wise_payment_receives', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('note');
        });
    }
};
