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
       Schema::create('subclasses', function (Blueprint $table) {
    $table->id('subclassID');
    $table->foreignId('classID')->constrained('classes', 'classID')->onDelete('cascade');
    $table->foreignId('teacherID')->nullable()->constrained('teachers')->onDelete('set null'); // class teacher
    $table->string('subclass_name', 50); // e.g. A, B, C
    $table->string('stream_code', 20)->nullable(); // e.g. 1A, 2B
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subclasses');
    }
};
