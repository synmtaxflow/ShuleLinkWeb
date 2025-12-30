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
        Schema::create('result_approvals', function (Blueprint $table) {
            $table->id('result_approvalID');
            $table->foreignId('examID')->constrained('examinations', 'examID')->onDelete('cascade');
            $table->unsignedBigInteger('role_id')->comment('Role ID that should approve at this step');
            $table->unsignedInteger('approval_order')->comment('Order of approval (1 = first, 2 = second, etc.)');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('Approval status at this step');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('Teacher ID who approved');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_comment')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['examID', 'approval_order']);
            $table->index(['role_id', 'status']);
            $table->index('examID');
            
            // Foreign key for role
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            
            // Foreign key for approved_by (teacher)
            $table->foreign('approved_by')->references('id')->on('teachers')->onDelete('set null');
            
            // Unique constraint: one approval order per exam
            $table->unique(['examID', 'approval_order'], 'unique_exam_approval_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_approvals');
    }
};
