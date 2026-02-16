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
        Schema::create('sgpm_action_plans', function (Blueprint $table) {
            $table->id('action_planID');
            $table->foreignId('objectiveID')->constrained('departmental_objectives', 'objectiveID')->onDelete('cascade');
            $table->string('title');
            $table->text('milestones')->nullable();
            $table->date('deadline');
            $table->string('output')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sgpm_action_plans');
    }
};
