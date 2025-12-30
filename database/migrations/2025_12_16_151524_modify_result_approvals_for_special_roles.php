<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop foreign key constraint first
        Schema::table('result_approvals', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });
        
        // Make role_id nullable and add special_role_type
        Schema::table('result_approvals', function (Blueprint $table) {
            // Make role_id nullable to allow special roles
            $table->unsignedBigInteger('role_id')->nullable()->change();
            
            // Add special_role_type column for class_teacher and coordinator
            $table->enum('special_role_type', ['class_teacher', 'coordinator'])->nullable()->after('role_id');
        });
        
        // Re-add foreign key constraint (nullable foreign keys are allowed in MySQL)
        DB::statement('ALTER TABLE result_approvals ADD CONSTRAINT result_approvals_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_approvals', function (Blueprint $table) {
            // Drop special_role_type column
            $table->dropColumn('special_role_type');
            
            // Make role_id not nullable again
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
        });
    }
};
