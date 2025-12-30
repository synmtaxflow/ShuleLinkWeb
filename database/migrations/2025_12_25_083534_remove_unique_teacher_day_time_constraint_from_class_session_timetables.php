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
        Schema::table('class_session_timetables', function (Blueprint $table) {
            // Drop the unique constraint that prevents teachers from having multiple sessions per day
            $table->dropUnique('unique_teacher_day_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_session_timetables', function (Blueprint $table) {
            // Re-add the unique constraint if needed to rollback
            $table->unique(['teacherID', 'day', 'start_time', 'end_time'], 'unique_teacher_day_time');
        });
    }
};
