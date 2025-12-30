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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id('attendanceID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->foreignId('subclassID')->constrained('subclasses', 'subclassID')->onDelete('cascade');
            $table->foreignId('studentID')->constrained('students', 'studentID')->onDelete('cascade');
            $table->foreignId('teacherID')->nullable()->constrained('teachers', 'id')->onDelete('set null');
            $table->date('attendance_date');
            $table->enum('status', ['Present', 'Absent', 'Late', 'Excused'])->default('Present');
            $table->text('remark')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['subclassID', 'attendance_date']);
            $table->index(['studentID', 'attendance_date']);
            $table->index('attendance_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

