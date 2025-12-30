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
        Schema::table('school_subjects', function (Blueprint $table) {
            $table->enum('student_status', ['Required', 'Optional'])->nullable()->after('status')->comment('Whether this subject is required or optional for students when added to a class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_subjects', function (Blueprint $table) {
            $table->dropColumn('student_status');
        });
    }
};
