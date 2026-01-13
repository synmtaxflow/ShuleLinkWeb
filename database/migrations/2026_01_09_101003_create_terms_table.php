<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates terms table to track academic terms within an academic year
     */
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id('termID');
            $table->foreignId('academic_yearID')->nullable()->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->string('term_name', 50)->comment('e.g., "First Term", "Second Term"');
            $table->integer('term_number')->comment('1 for First Term, 2 for Second Term, etc.');
            $table->integer('year')->comment('Academic year (e.g., 2024, 2025)');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['Active', 'Closed'])->default('Active');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['schoolID', 'year', 'term_number'], 't_sch_yr_term_idx');
            $table->index('status', 't_status_idx');
            $table->index('academic_yearID', 't_ay_idx');
            $table->unique(['schoolID', 'year', 'term_number'], 'unique_school_year_term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
