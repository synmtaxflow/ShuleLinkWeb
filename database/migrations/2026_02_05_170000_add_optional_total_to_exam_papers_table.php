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
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->unsignedInteger('optional_question_total')
                ->nullable()
                ->after('question_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->dropColumn('optional_question_total');
        });
    }
};
