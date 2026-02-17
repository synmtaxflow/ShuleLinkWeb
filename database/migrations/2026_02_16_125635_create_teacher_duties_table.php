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
        Schema::create('teacher_duties', function (Blueprint $table) {
            $table->id('teacher_dutyID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('teacherID');
            $table->unsignedBigInteger('termID')->nullable();
            $table->unsignedBigInteger('academic_yearID')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            $table->foreign('teacherID')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('termID')->references('termID')->on('terms')->onDelete('set null');
            $table->foreign('academic_yearID')->references('academic_yearID')->on('academic_years')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_duties');
    }
};
