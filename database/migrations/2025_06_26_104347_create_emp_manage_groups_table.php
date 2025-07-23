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
        Schema::create('emp_manage_groups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('groupId')->nullable()->index();
            $table->bigInteger('empId')->nullable()->index();
            $table->bigInteger('userId')->nullable()->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emp_manage_groups');
    }
};
