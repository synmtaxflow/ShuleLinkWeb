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
        Schema::create('session_timetable_definitions', function (Blueprint $table) {
            $table->bigIncrements('definitionID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->time('session_start_time');
            $table->time('session_end_time');
            $table->boolean('has_prepo')->default(false);
            $table->time('prepo_start_time')->nullable();
            $table->time('prepo_end_time')->nullable();
            $table->timestamps();

            // Index for faster queries
            $table->index('schoolID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_timetable_definitions');
    }
};
