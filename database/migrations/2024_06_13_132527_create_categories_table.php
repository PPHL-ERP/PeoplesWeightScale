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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('companyId')->nullable()->index();
            $table->longText('note')->nullable();
            $table->unsignedBigInteger('crBy')->nullable();
            $table->unsignedBigInteger('appBy')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('companyId')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('crBy')->references('id')->on('users')->onDelete('set null');
            $table->foreign('appBy')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};