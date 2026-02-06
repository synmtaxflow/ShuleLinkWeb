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
        Schema::table('exam_paper_optional_ranges', function (Blueprint $table) {
            $table->unsignedInteger('required_questions')
                ->default(1)
                ->after('total_marks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('exam_paper_optional_ranges', function (Blueprint $table) {
            $table->dropColumn('required_questions');
        });
    }
};
