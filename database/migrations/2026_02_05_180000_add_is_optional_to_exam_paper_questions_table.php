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
        Schema::table('exam_paper_questions', function (Blueprint $table) {
            $table->boolean('is_optional')->default(false)->after('question_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('exam_paper_questions', function (Blueprint $table) {
            $table->dropColumn('is_optional');
        });
    }
};
