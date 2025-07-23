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
        Schema::create('feed_receive_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('receiveId')->nullable();
            $table->bigInteger('productId')->nullable()->index();
            $table->decimal('trQty', 10, 2)->nullable(); // Changed to decimal for precision in quantity
            $table->decimal('rQty', 10, 2)->nullable(); // Changed to decimal for precision in received quantity
            $table->decimal('deviationQty', 10, 2)->nullable(); // Changed to decimal for precision in deviation
            $table->string('batchNo')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_receive_details');
    }
};
