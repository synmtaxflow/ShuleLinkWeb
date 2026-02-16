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
        Schema::create('departments', function (Blueprint $table) {
            $table->id('departmentID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->string('department_name');
            $table->enum('type', ['Academic', 'Administrative'])->default('Academic');
            $table->unsignedBigInteger('head_teacherID')->nullable();
            $table->unsignedBigInteger('head_staffID')->nullable();
            $table->timestamps();

            $table->foreign('head_teacherID')->references('id')->on('teachers')->onDelete('set null');
            $table->foreign('head_staffID')->references('id')->on('other_staff')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
