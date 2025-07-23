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
        Schema::create('debit_voucheritems', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('debitVoucherId')->index();
            $table->bigInteger('itemHeadId')->index();
            $table->double('amount', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debit_voucheritems');
    }
};
