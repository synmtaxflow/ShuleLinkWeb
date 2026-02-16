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
        Schema::create('sgpm_subtasks', function (Blueprint $table) {
            $table->id('subtaskID');
            $table->foreignId('taskID')->constrained('sgpm_tasks', 'taskID')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->float('weight_percentage'); // Part of the parent task's weight
            $table->enum('status', ['Draft', 'Submitted', 'Approved', 'Rejected'])->default('Draft');
            $table->date('due_date')->nullable();
            $table->text('evidence_remarks')->nullable();
            $table->string('evidence_file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sgpm_subtasks');
    }
};
