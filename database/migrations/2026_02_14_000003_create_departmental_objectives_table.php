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
        Schema::create('departmental_objectives', function (Blueprint $table) {
            $table->id('objectiveID');
            $table->foreignId('strategic_goalID')->constrained('strategic_goals', 'strategic_goalID')->onDelete('cascade');
            $table->foreignId('departmentID')->constrained('departments', 'departmentID')->onDelete('cascade');
            $table->string('kpi');
            $table->string('target_value');
            $table->decimal('budget', 15, 2)->nullable();
            $table->enum('status', ['Not Started', 'In Progress', 'Completed'])->default('Not Started');
            $table->unsignedBigInteger('assigned_hod_id')->nullable(); // Can be Teacher ID or Staff ID depending on dept head
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departmental_objectives');
    }
};
