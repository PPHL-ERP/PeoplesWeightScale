<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaction_images', function (Blueprint $table) {
            // Make transaction_id nullable
            if (Schema::hasColumn('transaction_images', 'transaction_id')) {
                $table->string('transaction_id', 128)->nullable()->change();
            }

            // Drop the old unique index that required transaction_id
            if (Schema::hasColumn('transaction_images', 'transaction_id')) {
                try {
                    $table->dropUnique('txn_cam_time_unique');
                } catch (\Exception $e) {
                    // index may not exist; ignore
                }
            }
        });
    }

    public function down()
    {
        Schema::table('transaction_images', function (Blueprint $table) {
            // Revert transaction_id to NOT NULL (set default empty string if NULLs present)
            if (Schema::hasColumn('transaction_images', 'transaction_id')) {
                // note: converting nullable->not-null requires cleaning existing nulls in production
                $table->string('transaction_id', 128)->nullable(false)->change();
            }
            // Recreate unique index (best-effort)
            try {
                $table->unique(['transaction_id', 'camera_no', 'captured_at'], 'txn_cam_time_unique');
            } catch (\Exception $e) {
                // ignore
            }
        });
    }
};
