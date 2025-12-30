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
        Schema::create('break_times', function (Blueprint $table) {
            $table->bigIncrements('break_timeID');
            $table->unsignedBigInteger('definitionID');
            $table->foreign('definitionID')->references('definitionID')->on('session_timetable_definitions')->onDelete('cascade');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('order')->default(1)->comment('Order of break time (1st, 2nd, etc.)');
            $table->timestamps();

            // Index for faster queries
            $table->index('definitionID');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_times');
    }
};
