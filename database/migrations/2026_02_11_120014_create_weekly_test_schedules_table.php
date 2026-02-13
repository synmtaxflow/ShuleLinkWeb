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
        Schema::create('weekly_test_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->string('test_type'); // 'weekly', 'monthly'
            $table->integer('week_number')->default(1);
            $table->string('day'); // Monday, Tuesday...
            
            // Scope
            $table->string('scope'); // 'school_wide', 'class', 'subclass'
            $table->unsignedBigInteger('scope_id')->nullable(); // classID or subclassID
            
            // Subject & Teacher
            $table->foreignId('subjectID')->constrained('school_subjects', 'subjectID')->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
            
            // Time
            $table->time('start_time');
            $table->time('end_time');

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            $table->index(['schoolID', 'test_type', 'scope', 'scope_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_test_schedules');
    }
};
