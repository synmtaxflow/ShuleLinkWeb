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
        Schema::create('exam_paper_questions', function (Blueprint $table) {
            $table->bigIncrements('exam_paper_questionID');
            $table->unsignedBigInteger('exam_paperID');
            $table->unsignedInteger('question_number');
            $table->string('question_description', 500);
            $table->unsignedInteger('marks');
            $table->timestamps();

            $table->foreign('exam_paperID')
                ->references('exam_paperID')
                ->on('exam_papers')
                ->onDelete('cascade');

            $table->index(['exam_paperID', 'question_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('exam_paper_questions');
    }
};
