<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bed_assignments', function (Blueprint $table) {
            $table->id('assignmentID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->foreignId('bedID')->constrained('beds', 'bedID')->onDelete('cascade');
            $table->foreignId('studentID')->constrained('students', 'studentID')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bed_assignments');
    }
};
