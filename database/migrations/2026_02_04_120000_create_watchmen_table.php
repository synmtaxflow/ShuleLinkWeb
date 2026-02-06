<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchmen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number')->unique();
            $table->string('email')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchmen');
    }
};
