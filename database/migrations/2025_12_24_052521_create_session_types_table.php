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
        Schema::create('session_types', function (Blueprint $table) {
            $table->bigIncrements('session_typeID');
            $table->unsignedBigInteger('definitionID');
            $table->foreign('definitionID')->references('definitionID')->on('session_timetable_definitions')->onDelete('cascade');
            $table->enum('type', ['single', 'double', 'triple', 'free', 'custom'])->default('single');
            $table->string('name', 100)->comment('Display name (e.g., "Single", "Double", or custom name)');
            $table->integer('minutes')->comment('Duration in minutes');
            $table->timestamps();

            // Index for faster queries
            $table->index('definitionID');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_types');
    }
};
