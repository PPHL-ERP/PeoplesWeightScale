<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Names & slogans
            $table->string('nameEn')->nullable();
            $table->string('nameBn')->nullable();
            $table->string('sloganEn')->nullable();
            $table->string('sloganBn')->nullable();

            // Contact details
            $table->string('mobile', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable()->unique(); // emails are usually unique
            $table->string('website')->nullable();

            // Branding / IDs
            $table->string('image')->nullable();   // company logo
            $table->string('tin')->nullable();     // Tax ID
            $table->string('bin')->nullable();     // Business ID

            // Addresses
            $table->string('addressEn')->nullable();
            $table->string('addressBn')->nullable();

            // Extra field
            $table->string('comEx')->nullable();

            // Status
            $table->enum('status', ['approved', 'declined'])
                  ->default('approved')
                  ->nullable();

            // Audit
            $table->softDeletes();  // deleted_at
            $table->timestamps();   // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
