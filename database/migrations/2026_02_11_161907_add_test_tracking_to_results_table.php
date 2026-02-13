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
        Schema::table('results', function (Blueprint $table) {
            $table->string('test_week')->nullable()->after('status')->comment('Format: Week of YYYY-MM-DD');
            $table->date('test_date')->nullable()->after('test_week');
            $table->unique(['studentID', 'examID', 'class_subjectID', 'test_week'], 'student_test_week_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropUnique('student_test_week_unique');
            $table->dropColumn(['test_week', 'test_date']);
        });
    }
};
