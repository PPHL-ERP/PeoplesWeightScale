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
            $table->bigIncrements('id'); // Primary key with auto-increment
            $table->bigInteger('companyId')->nullable();
            $table->string('name')->nullable()->index();
            $table->integer('isFarm')->nullable()->index();
            $table->tinyInteger('isSalesPoint')->default(0);
            $table->text('salesPointName')->nullable();
            $table->longText('description')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
            // You can add more indexes and foreign keys if needed
            // $table->index(['name']);
            // $table->foreign('other_id')->references('id')->on('other_table')->onDelete('cascade');
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
