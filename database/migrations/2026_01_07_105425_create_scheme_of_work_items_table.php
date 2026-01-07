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
        Schema::create('scheme_of_work_items', function (Blueprint $table) {
            $table->id('itemID');
            $table->foreignId('scheme_of_workID')->constrained('scheme_of_works', 'scheme_of_workID')->onDelete('cascade');
            $table->text('main_competence')->nullable();
            $table->text('specific_competences')->nullable();
            $table->text('learning_activities')->nullable();
            $table->text('specific_activities')->nullable();
            $table->string('month', 20)->nullable(); // January, February, etc.
            $table->integer('week')->nullable();
            $table->integer('number_of_periods')->nullable();
            $table->text('teaching_methods')->nullable();
            $table->text('teaching_resources')->nullable();
            $table->text('assessment_tools')->nullable();
            $table->text('references')->nullable();
            $table->text('remarks')->nullable(); // For manage section
            $table->integer('row_order')->default(0); // Order within the month
            $table->timestamps();
            
            $table->index(['scheme_of_workID', 'month', 'row_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_of_work_items');
    }
};
