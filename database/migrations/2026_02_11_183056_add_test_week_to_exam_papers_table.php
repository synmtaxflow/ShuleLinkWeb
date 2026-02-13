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
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->string('test_week')->nullable()->after('examID')->comment('Week/Month of the test (for weekly/monthly tests)');
            $table->date('test_date')->nullable()->after('test_week')->comment('Specific date of the test if applicable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->dropColumn(['test_week', 'test_date']);
        });
    }
};
