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
        Schema::create('w_vendor', function (Blueprint $table) {
            $table->id();
            $table->string('vId')->nullable();
            $table->string('vName')->nullable();
            $table->string(column: 'phone')->nullable();
            $table->string('address')->nullable();
            $table->string('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('w_vendor');
    }
};
