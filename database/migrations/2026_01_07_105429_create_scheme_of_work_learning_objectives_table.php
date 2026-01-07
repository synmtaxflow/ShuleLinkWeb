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
        Schema::create('scheme_of_work_learning_objectives', function (Blueprint $table) {
            $table->id('objectiveID');
            $table->foreignId('scheme_of_workID')->constrained('scheme_of_works', 'scheme_of_workID')->onDelete('cascade');
            $table->text('objective_text');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['scheme_of_workID', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_of_work_learning_objectives');
    }
};
