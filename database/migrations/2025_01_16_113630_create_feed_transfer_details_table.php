<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_transfer_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('transferId')->nullable();
            $table->bigInteger('productId')->nullable()->index();
            $table->string('qty')->nullable();
            $table->string('transferFor')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('feed_transfer_details');
    }
};
