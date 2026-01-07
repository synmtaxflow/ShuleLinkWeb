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
        // Drop the foreign key constraint first
        Schema::table('class_session_timetables', function (Blueprint $table) {
            $table->dropForeign(['teacherID']);
        });

        // Modify the column to be nullable
        Schema::table('class_session_timetables', function (Blueprint $table) {
            $table->unsignedBigInteger('teacherID')->nullable()->change();
        });

        // Re-add the foreign key constraint with nullable
        Schema::table('class_session_timetables', function (Blueprint $table) {
            $table->foreign('teacherID')->references('id')->on('teachers')->onDelete('set null');
        });
        
        // Note: The unique constraint on teacherID was already removed in a previous migration
        // (2025_12_25_083534_remove_unique_teacher_day_time_constraint_from_class_session_timetables)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint
        Schema::table('class_session_timetables', function (Blueprint $table) {
            $table->dropForeign(['teacherID']);
        });

        // Make it not nullable again (but this might fail if there are null values)
        Schema::table('class_session_timetables', function (Blueprint $table) {
            $table->unsignedBigInteger('teacherID')->nullable(false)->change();
        });

        // Re-add the foreign key constraint
        Schema::table('class_session_timetables', function (Blueprint $table) {
            $table->foreign('teacherID')->references('id')->on('teachers')->onDelete('cascade');
        });
    }
};
