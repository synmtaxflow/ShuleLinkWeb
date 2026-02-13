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
            $table->string('test_week_range')->nullable()->after('test_week');
            $table->unsignedBigInteger('weekly_test_schedule_id')->nullable()->after('examID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->dropColumn(['test_week_range', 'weekly_test_schedule_id']);
        });
    }
};
