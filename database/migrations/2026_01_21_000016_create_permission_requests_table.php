<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_requests', function (Blueprint $table) {
            $table->id('permissionID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->enum('requester_type', ['teacher', 'student'])->default('teacher');
            $table->unsignedBigInteger('teacherID')->nullable();
            $table->unsignedBigInteger('studentID')->nullable();
            $table->unsignedBigInteger('parentID')->nullable();
            $table->enum('time_mode', ['days', 'hours']);
            $table->integer('days_count')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('reason_type', ['medical', 'official', 'professional', 'emergency', 'other']);
            $table->text('reason_description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_response')->nullable();
            $table->string('admin_attachment_path')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('is_read_by_admin')->default(false);
            $table->boolean('is_read_by_requester')->default(true);
            $table->timestamps();

            $table->foreign('teacherID')->references('id')->on('teachers')->onDelete('set null');
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('set null');
            $table->foreign('parentID')->references('parentID')->on('parents')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_requests');
    }
};
