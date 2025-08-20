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
        Schema::create('designations', function (Blueprint $table) {
            $table->id();

            // Basic designation info
            $table->string('name')->nullable();

            // Department relationship
            $table->unsignedBigInteger('departmentId')->nullable()->index();

            // Leave allocation
            $table->tinyInteger('leaveCount')->nullable(); // up to 127 (signed) or 255 (unsigned)

            // Details
            $table->longText('description')->nullable();

            // Status (enum is supported in MySQL)
            $table->enum('status', ['approved', 'declined'])
                  ->default('approved')
                  ->nullable();

            // Soft delete & timestamps
            $table->softDeletes();
            $table->timestamps();

            // Optional FK if you have departments table
            // $table->foreign('departmentId')->references('id')->on('departments')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designations');
    }
};
