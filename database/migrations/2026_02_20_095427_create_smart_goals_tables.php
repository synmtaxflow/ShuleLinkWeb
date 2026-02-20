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
        // 1. Smart Goals Table
        Schema::create('smart_goals', function (Blueprint $table) {
            $table->id();
            $table->string('schoolID');
            $table->string('goal_name');
            $table->decimal('target_percentage', 5, 2);
            $table->date('deadline');
            $table->unsignedBigInteger('created_by');
            $table->string('status')->default('In Progress');
            $table->timestamps();
        });

        // 2. Goal Assigned Tasks Table (Admin -> Dept/Teacher/Staff)
        Schema::create('goal_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goal_id');
            $table->string('task_name');
            $table->string('assigned_to_type'); // Department, Teacher, Staff
            $table->unsignedBigInteger('assigned_to_id'); // departmentID, teacherID, or staffID
            $table->decimal('weight', 5, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->foreign('goal_id')->references('id')->on('smart_goals')->onDelete('cascade');
        });

        // 3. Goal Member Tasks Table (HOD -> Member)
        Schema::create('goal_member_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_task_id');
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('member_id'); // teacherID or staffID
            $table->string('member_type'); // Teacher or Staff
            $table->decimal('weight', 5, 2);
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->foreign('parent_task_id')->references('id')->on('goal_tasks')->onDelete('cascade');
        });

        // 4. Goal Subtasks Table (Member Breakdown)
        Schema::create('goal_subtasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_task_id')->nullable(); // Link to goal_member_tasks
            $table->unsignedBigInteger('direct_task_id')->nullable(); // Link to goal_tasks if assigned directly by admin
            $table->string('subtask_name');
            $table->text('description')->nullable();
            $table->decimal('weight', 5, 2); // 0-100% of the task
            $table->string('status')->default('undone'); // undone, done
            $table->boolean('is_sent_to_hod')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
        });

        // 5. Goal Notifications Table
        Schema::create('goal_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_notifications');
        Schema::dropIfExists('goal_subtasks');
        Schema::dropIfExists('goal_member_tasks');
        Schema::dropIfExists('goal_tasks');
        Schema::dropIfExists('smart_goals');
    }
};
