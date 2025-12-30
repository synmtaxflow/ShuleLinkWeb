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
        // Drop Spatie-related tables if they exist
        if (Schema::hasTable('role_has_permissions')) {
            Schema::drop('role_has_permissions');
        }
        if (Schema::hasTable('model_has_roles')) {
            Schema::drop('model_has_roles');
        }
        if (Schema::hasTable('model_has_permissions')) {
            Schema::drop('model_has_permissions');
        }

        // Modify permissions table to add roleId FK
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                // Drop existing unique constraint on name and guard_name
                $table->dropUnique(['name', 'guard_name']);
                
                // Add role_id column
                if (!Schema::hasColumn('permissions', 'role_id')) {
                    $table->unsignedBigInteger('role_id')->nullable()->after('guard_name');
                    $table->foreign('role_id')
                        ->references('id')
                        ->on('roles')
                        ->onDelete('cascade');
                }
                
                // Add unique constraint on role_id and name (one permission name per role)
                $table->unique(['role_id', 'name'], 'permissions_role_id_name_unique');
            });
        } else {
            // Create permissions table if it doesn't exist
            Schema::create('permissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name')->default('web');
                $table->unsignedBigInteger('role_id')->nullable();
                $table->timestamps();

                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');

                // Unique constraint: one permission name per role
                $table->unique(['role_id', 'name'], 'permissions_role_id_name_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                // Drop foreign key
                if (Schema::hasColumn('permissions', 'role_id')) {
                    $table->dropForeign(['role_id']);
                    $table->dropColumn('role_id');
                }
                
                // Restore original unique constraint
                $table->unique(['name', 'guard_name']);
            });
        }
    }
};
