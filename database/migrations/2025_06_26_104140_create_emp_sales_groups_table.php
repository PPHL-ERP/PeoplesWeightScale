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
        Schema::create('emp_sales_groups', function (Blueprint $table) {
            $table->id();
            $table->string('groupName')->nullable();
            $table->string('groupLocation')->nullable();
            $table->bigInteger('groupLeader')->nullable()->index();
            $table->bigInteger('groupSup')->nullable()->index();
            $table->text('note')->nullable();
            $table->string('status')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emp_sales_groups');
    }
};