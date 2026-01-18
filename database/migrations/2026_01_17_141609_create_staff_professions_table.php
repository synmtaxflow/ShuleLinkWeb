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
        Schema::create('staff_professions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID')->nullable(); // link to schools table
            $table->string('name'); // IT, Accountant, HR, etc.
            $table->text('description')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            
            // Unique constraint: same profession name can't exist twice in same school
            $table->unique(['schoolID', 'name'], 'staff_professions_school_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_professions');
    }
};
