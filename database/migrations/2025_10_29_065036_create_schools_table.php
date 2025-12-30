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
        Schema::create('schools', function (Blueprint $table) {
            $table->id('schoolID');
            $table->string('school_name', 150);
            $table->string('registration_number', 50)->unique()->nullable();
            $table->enum('school_type', ['Primary', 'Secondary']);
            $table->enum('ownership', ['Public', 'Private']);
            $table->string('region', 100);
            $table->string('district', 100);
            $table->string('ward', 100)->nullable();
            $table->string('village', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->year('established_year')->nullable();
            $table->string('school_logo', 255)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
