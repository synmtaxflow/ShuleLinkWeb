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
        // Check if column doesn't exist before adding it
        if (!Schema::hasColumn('student_session_attendance', 'remark')) {
            Schema::table('student_session_attendance', function (Blueprint $table) {
                $table->text('remark')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if column exists before dropping it
        if (Schema::hasColumn('student_session_attendance', 'remark')) {
            Schema::table('student_session_attendance', function (Blueprint $table) {
                $table->dropColumn('remark');
            });
        }
    }
};
