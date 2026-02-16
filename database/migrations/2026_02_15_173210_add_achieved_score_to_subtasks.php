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
        Schema::table('sgpm_subtasks', function (Blueprint $table) {
            $table->float('achieved_score')->default(0)->after('weight_percentage');
            $table->text('hod_comments')->nullable()->after('evidence_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sgpm_subtasks', function (Blueprint $table) {
            $table->dropColumn(['achieved_score', 'hod_comments']);
        });
    }
};
