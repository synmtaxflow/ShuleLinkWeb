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
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->text('reflection')->nullable()->after('remarks');
            $table->text('evaluation')->nullable()->after('reflection');
            $table->text('teacher_signature')->nullable()->after('evaluation')->comment('Base64 encoded signature image');
            $table->text('supervisor_signature')->nullable()->after('teacher_signature')->comment('Base64 encoded signature image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->dropColumn(['reflection', 'evaluation', 'teacher_signature', 'supervisor_signature']);
        });
    }
};
