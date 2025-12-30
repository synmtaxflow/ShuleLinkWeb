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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id('roomID');
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->foreignId('blockID')->constrained('blocks', 'blockID')->onDelete('cascade');
            $table->string('room_name', 100);
            $table->string('room_number', 50);
            $table->integer('capacity')->default(0);
            $table->integer('tables')->default(0);
            $table->integer('chairs')->default(0);
            $table->integer('cabinets')->default(0);
            $table->integer('wardrobes')->default(0);
            $table->integer('other_items')->default(0);
            $table->text('other_items_description')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
