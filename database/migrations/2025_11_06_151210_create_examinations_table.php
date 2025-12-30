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
        Schema::create('examinations', function (Blueprint $table) {
            $table->id('examID');
            $table->string('exam_name', 200);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['ongoing', 'awaiting_results', 'results_available'])->default('ongoing');
            $table->enum('exam_type', [
                'school_wide_all_subjects',
                'specific_classes_all_subjects',
                'school_wide_specific_subjects',
                'specific_classes_specific_subjects'
            ]);
            $table->foreignId('schoolID')->nullable()->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->year('year');
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};
