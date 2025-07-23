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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->bigInteger('employeeId')->nullable();
            $table->string('email')->unique();
            $table->string('isSuperAdmin')->nullable();
            $table->string('isAdmin')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('isBanned')->nullable();
            $table->bigInteger('userManageProductId')->nullable();
            $table->string('image')->nullable();
            $table->string('ipAddress')->nullable();
            $table->string('signature')->nullable();
            $table->longText('note')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
