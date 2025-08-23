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
        Schema::table('user_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('userId')->nullable(false)->change();
            $table->unsignedBigInteger('roleId')->nullable(false)->change();
            $table->unique(['userId','roleId']);
            $table->foreign('userId')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('roleId')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('roleId')->nullable(false)->change();
            $table->unsignedBigInteger('permissionId')->nullable(false)->change();
            $table->unique(['roleId','permissionId']);
            $table->foreign('roleId')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('permissionId')->references('id')->on('permissions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_has_roles', function (Blueprint $table) {
            $table->dropUnique(['userId','roleId']);
            $table->dropForeign(['userId']);
            $table->dropForeign(['roleId']);
        });

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropUnique(['roleId','permissionId']);
            $table->dropForeign(['roleId']);
            $table->dropForeign(['permissionId']);
        });
    }
};