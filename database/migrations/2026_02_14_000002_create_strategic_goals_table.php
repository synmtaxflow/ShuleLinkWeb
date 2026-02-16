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
        Schema::create('strategic_goals', function (Blueprint $table) {
            $table->id('strategic_goalID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('kpi');
            $table->string('target_value');
            $table->date('timeline_date');
            $table->string('supporting_document')->nullable();
            $table->enum('status', ['Draft', 'Published', 'Completed'])->default('Draft');
            $table->foreignId('created_by')->constrained('users', 'id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategic_goals');
    }
};
