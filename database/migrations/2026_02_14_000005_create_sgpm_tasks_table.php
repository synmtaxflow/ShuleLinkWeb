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
        Schema::create('sgpm_tasks', function (Blueprint $table) {
            $table->id('taskID');
            $table->foreignId('action_planID')->constrained('sgpm_action_plans', 'action_planID')->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users', 'id')->onDelete('cascade');
            $table->string('kpi');
            $table->integer('weight')->default(1);
            $table->text('description')->nullable();
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Approved', 'Rejected'])->default('Pending');
            $table->date('due_date');
            $table->timestamp('completion_date')->nullable();
            
            // Performance fields
            $table->float('score_completion')->default(0);
            $table->float('score_kpi')->default(0);
            $table->float('score_timeliness')->default(0);
            $table->float('total_score')->default(0);
            
            $table->text('hod_comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sgpm_tasks');
    }
};
