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
        Schema::create('beds', function (Blueprint $table) {
            $table->id('bedID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->foreignId('blockID')->constrained('blocks', 'blockID')->onDelete('cascade');
            $table->foreignId('roomID')->nullable()->constrained('rooms', 'roomID')->onDelete('cascade');
            $table->string('bed_number', 50)->nullable();
            $table->enum('has_mattress', ['Yes', 'No'])->default('No');
            $table->enum('status', ['Available', 'Occupied', 'Maintenance', 'Inactive'])->default('Available');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
