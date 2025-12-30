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
        Schema::table('exam_attendance', function (Blueprint $table) {
            $table->enum('status', ['Present', 'Absent'])->default('Absent')->after('studentID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attendance', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
