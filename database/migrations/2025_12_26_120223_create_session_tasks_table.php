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
        Schema::create('session_tasks', function (Blueprint $table) {
            $table->id('session_taskID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->unsignedBigInteger('session_timetableID');
            $table->foreign('session_timetableID')->references('session_timetableID')->on('class_session_timetables')->onDelete('cascade');
            $table->foreignId('teacherID')->constrained('teachers', 'id')->onDelete('cascade');
            $table->date('task_date');
            $table->text('task_description');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_comment')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['session_timetableID', 'task_date']);
            $table->index(['teacherID', 'task_date']);
            $table->index(['status', 'task_date']);
            $table->index('task_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_tasks');
    }
};
