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
        Schema::table('weekly_test_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('examID')->nullable()->after('schoolID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_test_schedules', function (Blueprint $table) {
            $table->dropColumn('examID');
        });
    }
};
