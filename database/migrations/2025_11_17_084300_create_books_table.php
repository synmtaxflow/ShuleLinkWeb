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
        Schema::create('books', function (Blueprint $table) {
            $table->id('bookID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->foreignId('classID')->constrained('classes', 'classID')->onDelete('cascade');
            $table->foreignId('subjectID')->constrained('school_subjects', 'subjectID')->onDelete('cascade');
            $table->string('book_title', 255);
            $table->string('author', 255)->nullable();
            $table->string('isbn', 50)->nullable();
            $table->string('publisher', 255)->nullable();
            $table->year('publication_year')->nullable();
            $table->integer('total_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->integer('issued_quantity')->default(0);
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};

