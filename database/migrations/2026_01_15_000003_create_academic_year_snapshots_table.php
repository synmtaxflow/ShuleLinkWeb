<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates academic_year_snapshots table to store complete snapshot data when closing academic year
     * This will store JSON data for: classes, subclasses, subjects, fees, students count, etc.
     */
    public function up(): void
    {
        Schema::create('academic_year_snapshots', function (Blueprint $table) {
            $table->id('snapshotID');
            $table->foreignId('academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->string('snapshot_type', 50)->comment('Type: classes, subclasses, subjects, fees, students_count, etc.');
            $table->json('snapshot_data')->comment('Complete JSON data snapshot');
            $table->text('description')->nullable();
            $table->integer('total_records')->default(0)->comment('Total number of records in snapshot');
            $table->timestamps();
            
            // Indexes
            $table->index(['academic_yearID', 'snapshot_type']);
            $table->index('snapshot_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_year_snapshots');
    }
};


