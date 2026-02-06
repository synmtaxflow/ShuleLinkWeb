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
            $table->unsignedInteger('optional_range_number')
                ->nullable()
                ->after('is_optional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('exam_paper_questions', function (Blueprint $table) {
            $table->dropColumn('optional_range_number');
        });
    }
};
