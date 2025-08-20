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
        Schema::create('user_manages_sectors', function (Blueprint $table) {
            $table->id();

            // Use unsignedBigInteger for MySQL FK consistency
            $table->unsignedBigInteger('sectorId')->nullable()->index();
            $table->unsignedBigInteger('userId')->nullable()->index();

            $table->timestamps();

            // Optional: foreign key constraints (if related tables exist)
            // $table->foreign('sectorId')->references('id')->on('sectors')->nullOnDelete();
            // $table->foreign('userId')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_manages_sectors');
    }
};
