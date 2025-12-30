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
            // Drop old grade_requirement column if it exists
            if (Schema::hasColumn('subclasses', 'grade_requirement')) {
                $table->dropColumn('grade_requirement');
            }
            
            // Add new columns for grade range
            $table->string('first_grade', 50)->nullable()->after('status')->comment('Starting grade/division for students to join this subclass');
            $table->string('final_grade', 50)->nullable()->after('first_grade')->comment('Ending grade/division for students to join this subclass');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subclasses', function (Blueprint $table) {
            $table->dropColumn(['first_grade', 'final_grade']);
            
            // Restore old column if needed
            if (!Schema::hasColumn('subclasses', 'grade_requirement')) {
                $table->string('grade_requirement', 50)->nullable()->after('status');
            }
        });
    }
};
