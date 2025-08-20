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
        Schema::create('log_activities', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT (MySQL)

            $table->string('subject');
            $table->string('url');
            $table->string('method');
            $table->string('ip', 45); // supports IPv6 also

            $table->string('agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // Geo precision
            $table->decimal('latitude', 10, 7)->nullable();   // e.g., 23.8103312
            $table->decimal('longitude', 10, 7)->nullable();  // e.g., 90.4125218

            $table->timestamps();

            // Optional FK if you want link with users
            // $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_activities');
    }
};
