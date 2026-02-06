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
        Schema::create('exam_paper_notifications', function (Blueprint $table) {
            $table->bigIncrements('exam_paper_notificationID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('exam_paperID');
            $table->unsignedBigInteger('teacherID');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('schoolID')
                ->references('schoolID')
                ->on('schools')
                ->onDelete('cascade');

            $table->foreign('exam_paperID')
                ->references('exam_paperID')
                ->on('exam_papers')
                ->onDelete('cascade');

            $table->foreign('teacherID')
                ->references('id')
                ->on('teachers')
                ->onDelete('cascade');

            $table->index(['schoolID', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('exam_paper_notifications');
    }
};
