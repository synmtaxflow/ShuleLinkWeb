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
        // Check if table exists, if not create it
        if (!Schema::hasTable('exam_papers')) {
            Schema::create('exam_papers', function (Blueprint $table) {
                $table->id('exam_paperID');
                $table->foreignId('examID')->constrained('examinations', 'examID')->onDelete('cascade');
                $table->foreignId('class_subjectID')->constrained('class_subjects', 'class_subjectID')->onDelete('cascade');
                $table->foreignId('teacherID')->constrained('teachers')->onDelete('cascade');
                $table->string('file_path')->nullable()->comment('Path to uploaded exam paper file');
                $table->text('question_content')->nullable()->comment('Text content for created exam questions');
                $table->enum('upload_type', ['upload', 'create'])->default('upload')->comment('Type of exam paper: upload file or create questions');
                $table->enum('status', ['wait_approval', 'approved', 'rejected'])->default('wait_approval');
                $table->text('rejection_reason')->nullable()->comment('Reason for rejection if status is rejected');
                $table->text('approval_comment')->nullable()->comment('Comment from admin when approving');
                $table->timestamps();

                // Indexes for better query performance
                $table->index('examID');
                $table->index('class_subjectID');
                $table->index('teacherID');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_papers');
    }
};


