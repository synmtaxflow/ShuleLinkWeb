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
        Schema::table('subclasses', function (Blueprint $table) {
            $table->string('grade_requirement', 50)->nullable()->after('status')->comment('Required grade/division for students to join this subclass');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subclasses', function (Blueprint $table) {
            $table->dropColumn('grade_requirement');
        });
    }
};
