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
        // Add foreign key constraint after staff_professions table exists
        Schema::table('other_staff', function (Blueprint $table) {
            if (Schema::hasTable('staff_professions')) {
                $table->foreign('profession_id')
                    ->references('id')
                    ->on('staff_professions')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('other_staff', function (Blueprint $table) {
            // Drop foreign key if exists
            try {
                $table->dropForeign(['profession_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist, ignore
            }
        });
    }
};
