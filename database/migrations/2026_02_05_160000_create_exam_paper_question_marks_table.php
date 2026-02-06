<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('exam_paper_question_marks', function (Blueprint $table) {
            $table->bigIncrements('exam_paper_question_markID');
            $table->unsignedBigInteger('exam_paper_questionID');
            $table->unsignedBigInteger('studentID');
            $table->unsignedBigInteger('examID');
            $table->unsignedBigInteger('class_subjectID');
            $table->decimal('marks', 6, 2)->default(0);
            $table->timestamps();

            $table->foreign('exam_paper_questionID')
                ->references('exam_paper_questionID')
                ->on('exam_paper_questions')
                ->onDelete('cascade');

            $table->foreign('studentID')
                ->references('studentID')
                ->on('students')
                ->onDelete('cascade');

            $table->foreign('examID')
                ->references('examID')
                ->on('examinations')
                ->onDelete('cascade');

            $table->foreign('class_subjectID')
                ->references('class_subjectID')
                ->on('class_subjects')
                ->onDelete('cascade');

            $table->unique(['exam_paper_questionID', 'studentID']);
            $table->index(['examID', 'class_subjectID', 'studentID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('exam_paper_question_marks');
    }
};
