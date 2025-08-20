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
        Schema::create('sectors', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT in MySQL

            // Foreign key to companies
            $table->unsignedBigInteger('companyId')->nullable()->index();

            $table->string('name')->nullable()->index();
            $table->boolean('isFarm')->default(0)->index(); // better than integer for true/false
            $table->boolean('isSalesPoint')->default(0);
            $table->string('salesPointName')->nullable();

            $table->longText('description')->nullable();
            $table->string('status')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Optional: enforce FK if companies table exists
            // $table->foreign('companyId')->references('id')->on('companies')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sectors');
    }
};
