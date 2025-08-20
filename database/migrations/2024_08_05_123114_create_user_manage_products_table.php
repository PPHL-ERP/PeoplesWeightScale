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
        Schema::create('user_manage_products', function (Blueprint $table) {
            $table->id();

            // Use unsignedBigInteger if linking to another table’s primary key
            $table->unsignedBigInteger('productCategoryId')->nullable();
            $table->unsignedBigInteger('userId')->nullable();

            $table->timestamps();

            // Indexes for faster queries
            $table->index('productCategoryId');
            $table->index('userId');

            // Optional: Add foreign keys if related tables exist
            // $table->foreign('productCategoryId')->references('id')->on('product_categories')->nullOnDelete();
            // $table->foreign('userId')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_manage_products');
    }
};
