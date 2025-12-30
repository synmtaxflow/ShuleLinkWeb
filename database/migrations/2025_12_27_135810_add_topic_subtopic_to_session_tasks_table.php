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
        Schema::table('session_tasks', function (Blueprint $table) {
            $table->string('topic')->nullable()->after('task_date');
            $table->string('subtopic')->nullable()->after('topic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_tasks', function (Blueprint $table) {
            $table->dropColumn(['topic', 'subtopic']);
        });
    }
};
