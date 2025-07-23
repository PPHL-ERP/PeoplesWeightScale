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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('doj')->nullable()->index();
            $table->string('image')->nullable();
            $table->string('email')->nullable()->unique(); // Add unique constraint to 'email' column
            $table->string('phone_number')->nullable()->unique(14); // You can index columns like this ->index('phone_number')
            $table->string('family_number')->nullable();
            $table->string('nid')->nullable();
            $table->string('passport')->nullable();
            $table->date('dob')->nullable()->index();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('current_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};