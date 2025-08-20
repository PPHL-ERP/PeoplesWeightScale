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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Basic product info
            $table->string('productId')->nullable()->unique();
            $table->string('productName')->nullable();
            $table->string('productType')->nullable();
            $table->unsignedBigInteger('sn')->nullable();
            $table->string('qrCode')->nullable()->unique();
            $table->string('batchNo')->nullable();

            // Relations (FKs optional but recommended)
            $table->unsignedBigInteger('companyId')->nullable()->index();
            $table->unsignedBigInteger('categoryId')->nullable()->index();
            $table->unsignedBigInteger('subCategoryId')->nullable()->index();
            $table->unsignedBigInteger('childCategoryId')->nullable()->index();
            $table->unsignedBigInteger('unitId')->nullable();

            // Product details
            $table->string('image')->nullable();
            $table->decimal('basePrice', 10, 2)->nullable();
            $table->string('productForm')->nullable();
            $table->string('warranty')->nullable();
            $table->string('minStock')->nullable(); // could also be integer
            $table->longText('description')->nullable();

            // Status / audit info
            $table->string('crBy')->nullable();
            $table->string('appBy')->nullable();
            $table->string('status')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Optional foreign keys if related tables exist
            // $table->foreign('companyId')->references('id')->on('companies')->nullOnDelete();
            // $table->foreign('categoryId')->references('id')->on('categories')->nullOnDelete();
            // $table->foreign('subCategoryId')->references('id')->on('sub_categories')->nullOnDelete();
            // $table->foreign('childCategoryId')->references('id')->on('child_categories')->nullOnDelete();
            // $table->foreign('unitId')->references('id')->on('units')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
