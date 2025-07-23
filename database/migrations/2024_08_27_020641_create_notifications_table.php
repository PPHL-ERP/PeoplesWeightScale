<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companyId')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('userId')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->text('message')->nullable();
            $table->unsignedBigInteger('crBy')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('crBy')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
