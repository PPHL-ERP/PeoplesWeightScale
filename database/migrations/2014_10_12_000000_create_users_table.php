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

            // Basic info
            $table->string('name');
            $table->string('username')->nullable();
            $table->unsignedBigInteger('employeeId')->nullable()->index();

            // Auth
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Roles / permissions (better as tinyInteger or boolean)
            $table->boolean('isSuperAdmin')->default(false);
            $table->boolean('isAdmin')->default(false);
            $table->boolean('isBanned')->default(false);

            // Relations
            $table->unsignedBigInteger('userManageProductId')->nullable()->index();

            // Profile
            $table->string('image')->nullable();
            $table->string('ipAddress')->nullable();
            $table->string('signature')->nullable();
            $table->longText('note')->nullable();

            // Status
            $table->tinyInteger('status')->nullable();

            // System fields
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();

            // Optional FKs if related tables exist
            // $table->foreign('employeeId')->references('id')->on('employees')->nullOnDelete();
            // $table->foreign('userManageProductId')->references('id')->on('user_manage_products')->nullOnDelete();
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
