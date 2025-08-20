<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mono_weight_transactions', function (Blueprint $table) {
            $table->id(); // BIGINT unsigned AUTO_INCREMENT in MySQL

            $table->string('transaction_id')->nullable()->index();
            $table->string('weight_type')->nullable();
            $table->string('transfer_type')->nullable();
            $table->string('select_mode')->nullable();

            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('material')->nullable();
            $table->string('productType')->nullable();

            $table->decimal('gross_weight', 12, 2)->nullable();
            $table->timestamp('gross_time')->nullable();
            $table->string('gross_operator', 100)->nullable();

            $table->decimal('tare_weight', 12, 2)->nullable();
            $table->timestamp('tare_time')->nullable();
            $table->string('tare_operator', 100)->nullable();

            $table->decimal('volume', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('amount', 12, 2)->nullable();

            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('real_net', 12, 2)->nullable();

            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('sale_id')->nullable();
            $table->string('purchase_id')->nullable();

            $table->unsignedBigInteger('sector_id')->nullable()->index();
            $table->string('sector_name')->nullable();

            $table->text('note')->nullable();
            $table->text('others')->nullable();
            $table->string('username')->nullable();
            $table->string('status')->nullable(); // e.g., Unfinished, Finished, Reject

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_transactions');
    }
};
