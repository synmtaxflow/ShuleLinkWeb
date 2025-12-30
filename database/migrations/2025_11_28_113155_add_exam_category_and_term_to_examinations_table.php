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
            $table->enum('exam_category', ['school_exams', 'special_exams'])->nullable()->after('exam_name')->comment('Category of exam: school_exams or special_exams');
            $table->enum('term', ['first_term', 'second_term'])->nullable()->after('exam_category')->comment('Term when exam is conducted: first_term or second_term');
            $table->json('except_class_ids')->nullable()->after('term')->comment('Array of class IDs excluded from school exams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->dropColumn(['exam_category', 'term', 'except_class_ids']);
        });
    }
};
