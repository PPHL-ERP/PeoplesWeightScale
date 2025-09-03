<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transaction_id', 128)->index();
            $table->string('camera_no', 16);
            $table->timestampTz('captured_at');
            $table->text('image_path');
            $table->string('storage_backend', 32)->default('local');
            $table->string('content_type', 64)->nullable();
            $table->integer('size_bytes')->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('ingest_status', 32)->default('stored');
            $table->json('extra_meta')->nullable();
            $table->timestamps();

            $table->unique(['transaction_id', 'camera_no', 'captured_at'], 'txn_cam_time_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_images');
    }
};
