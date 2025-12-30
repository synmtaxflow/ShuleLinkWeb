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
       Schema::create('classes', function (Blueprint $table) {
    $table->id('classID');
    $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
    $table->foreignId('teacherID')->nullable()->constrained('teachers')->onDelete('set null'); // coordinator
    $table->string('class_name', 100); // e.g. Form 1, Standard 7
    $table->string('description')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
