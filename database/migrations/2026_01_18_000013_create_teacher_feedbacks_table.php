<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_feedbacks', function (Blueprint $table) {
            $table->bigIncrements('feedbackID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('teacherID');
            $table->string('type', 20); // suggestion | incident
            $table->text('message');
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->text('admin_response')->nullable();
            $table->date('response_due_date')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->boolean('is_read_by_admin')->default(false);
            $table->boolean('is_read_by_teacher')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_feedbacks');
    }
};
