<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates student_promotions table to track student promotions when closing academic year
     */
    public function up(): void
    {
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id('promotionID');
            $table->foreignId('studentID')->constrained('students', 'studentID')->onDelete('cascade');
            $table->foreignId('from_academic_yearID')->constrained('academic_years', 'academic_yearID')->onDelete('cascade');
            $table->foreignId('to_academic_yearID')->nullable()->constrained('academic_years', 'academic_yearID')->onDelete('set null');
            $table->foreignId('from_classID')->nullable()->constrained('classes', 'classID')->onDelete('set null');
            $table->foreignId('from_subclassID')->nullable()->constrained('subclasses', 'subclassID')->onDelete('set null');
            $table->foreignId('to_classID')->nullable()->constrained('classes', 'classID')->onDelete('set null');
            $table->foreignId('to_subclassID')->nullable()->constrained('subclasses', 'subclassID')->onDelete('set null');
            $table->enum('promotion_type', ['Promoted', 'Repeated', 'Graduated', 'Transferred'])->default('Promoted');
            $table->date('promotion_date');
            $table->foreignId('promoted_by')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['studentID', 'from_academic_yearID']);
            $table->index(['to_academic_yearID']);
            $table->index('promotion_type');
            $table->index('promotion_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};


