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
        Schema::table('exam_hall_supervisors', function (Blueprint $table) {
            // Add subjectID to link supervisor to specific subject
            $table->foreignId('subjectID')->nullable()->after('exam_hallID')->constrained('school_subjects', 'subjectID')->onDelete('cascade');
            
            // Add exam_timetableID to link supervisor to specific timetable slot
            $table->foreignId('exam_timetableID')->nullable()->after('subjectID')->constrained('exam_timetable', 'exam_timetableID')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_hall_supervisors', function (Blueprint $table) {
            $table->dropForeign(['subjectID']);
            $table->dropForeign(['exam_timetableID']);
            $table->dropColumn(['subjectID', 'exam_timetableID']);
        });
    }
};





