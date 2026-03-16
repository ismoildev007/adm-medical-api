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
        // Add ID to permission_role
        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropForeign(['role_name']);
            $table->dropForeign(['permission_name']);
            $table->dropPrimary(['role_name', 'permission_name']);
        });

        Schema::table('permission_role', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
            $table->unique(['role_name', 'permission_name']);
            
            $table->foreign('role_name')
                ->references('name')->on('roles')
                ->onDelete('cascade');

            $table->foreign('permission_name')
                ->references('name')->on('permissions')
                ->onDelete('cascade');
        });

        // Add ID to role_user
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropForeign(['user_name']);
            $table->dropForeign(['role_name']);
            $table->dropPrimary(['user_name', 'role_name']);
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
            $table->unique(['user_name', 'role_name']);

            $table->foreign('user_name')
                ->references('username')->on('users')
                ->onDelete('cascade');

            $table->foreign('role_name')
                ->references('name')->on('roles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropForeign(['role_name']);
            $table->dropForeign(['permission_name']);
            $table->dropUnique(['role_name', 'permission_name']);
            $table->dropColumn('id');
            
            $table->primary(['role_name', 'permission_name']);
            
            $table->foreign('role_name')
                ->references('name')->on('roles')
                ->onDelete('cascade');

            $table->foreign('permission_name')
                ->references('name')->on('permissions')
                ->onDelete('cascade');
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->dropForeign(['user_name']);
            $table->dropForeign(['role_name']);
            $table->dropUnique(['user_name', 'role_name']);
            $table->dropColumn('id');

            $table->primary(['user_name', 'role_name']);

            $table->foreign('user_name')
                ->references('username')->on('users')
                ->onDelete('cascade');

            $table->foreign('role_name')
                ->references('name')->on('roles')
                ->onDelete('cascade');
        });
    }
};
