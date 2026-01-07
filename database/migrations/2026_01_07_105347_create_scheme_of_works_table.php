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
        Schema::create('scheme_of_works', function (Blueprint $table) {
            $table->id('scheme_of_workID');
            $table->foreignId('class_subjectID')->constrained('class_subjects', 'class_subjectID')->onDelete('cascade');
            $table->integer('year');
            $table->enum('status', ['Draft', 'Active', 'Archived'])->default('Draft');
            $table->foreignId('created_by')->nullable()->constrained('teachers', 'id')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['class_subjectID', 'year']);
            $table->unique(['class_subjectID', 'year']); // One scheme per class subject per year
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_of_works');
    }
};
