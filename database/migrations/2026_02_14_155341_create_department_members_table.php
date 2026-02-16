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
        Schema::create('department_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departmentID')->constrained('departments', 'departmentID')->onDelete('cascade');
            $table->unsignedBigInteger('teacherID')->nullable();
            $table->unsignedBigInteger('staffID')->nullable();
            $table->timestamps();

            $table->foreign('teacherID')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('staffID')->references('id')->on('other_staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_members');
    }
};
