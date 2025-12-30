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
        Schema::create('class_teacher_approvals', function (Blueprint $table) {
            $table->id('class_teacher_approvalID');
            $table->foreignId('result_approvalID')->constrained('result_approvals', 'result_approvalID')->onDelete('cascade');
            $table->foreignId('subclassID')->constrained('subclasses', 'subclassID')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('Teacher ID who approved');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_comment')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['result_approvalID', 'status']);
            $table->index('subclassID');
            $table->index('approved_by');
            
            // Foreign key for approved_by (teacher)
            $table->foreign('approved_by')->references('id')->on('teachers')->onDelete('set null');
            
            // Unique constraint: one approval per subclass per result_approval
            $table->unique(['result_approvalID', 'subclassID'], 'unique_result_approval_subclass');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_teacher_approvals');
    }
};
