<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->enum('student_shifting_option', ['none', 'internal_class', 'external_class'])
                  ->default('none')
                  ->after('exam_type')
                  ->comment('Student shifting option: none (no shifting), internal_class (shift within same class level), external_class (shift to different class level)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->dropColumn('student_shifting_option');
        });
    }
};
