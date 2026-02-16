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
        Schema::create('sgpm_evidence', function (Blueprint $table) {
            $table->id('evidenceID');
            $table->foreignId('taskID')->constrained('sgpm_tasks', 'taskID')->onDelete('cascade');
            $table->string('file_path');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sgpm_evidence');
    }
};
