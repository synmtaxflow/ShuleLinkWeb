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
        // Update existing roles table to be compatible with Spatie
        Schema::table('roles', function (Blueprint $table) {
            // Spatie uses 'name' instead of 'role_name', but we'll keep both for backward compatibility
            if (!Schema::hasColumn('roles', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('roles', 'guard_name')) {
                $table->string('guard_name')->default('web')->after('name');
            }
        });

        // Migrate existing role_name to name
        \Illuminate\Support\Facades\DB::statement("UPDATE roles SET name = role_name WHERE name IS NULL");
        
        // Add unique constraint for Spatie
        Schema::table('roles', function (Blueprint $table) {
            $table->unique(['name', 'guard_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'name')) {
                $table->dropUnique(['name', 'guard_name']);
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('roles', 'guard_name')) {
                $table->dropColumn('guard_name');
            }
        });
    }
};

