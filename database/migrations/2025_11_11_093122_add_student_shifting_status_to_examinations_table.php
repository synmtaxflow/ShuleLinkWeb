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
            $table->enum('student_shifting_status', ['none', 'internal', 'external'])
                  ->default('none')
                  ->after('student_shifting_option')
                  ->comment('Student shifting status: none (no shifting allowed), internal (shift within same class level, e.g., Form Four A to Form Four B), external (shift to different class level, e.g., Form Three B to Form Four A)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->dropColumn('student_shifting_status');
        });
    }
};
