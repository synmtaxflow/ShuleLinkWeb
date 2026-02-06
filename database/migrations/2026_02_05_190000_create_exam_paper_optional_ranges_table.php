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
        Schema::create('exam_paper_optional_ranges', function (Blueprint $table) {
            $table->bigIncrements('exam_paper_optional_rangeID');
            $table->unsignedBigInteger('exam_paperID');
            $table->unsignedInteger('range_number');
            $table->unsignedInteger('total_marks');
            $table->timestamps();

            $table->foreign('exam_paperID')
                ->references('exam_paperID')
                ->on('exam_papers')
                ->onDelete('cascade');

            $table->unique(['exam_paperID', 'range_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('exam_paper_optional_ranges');
    }
};
