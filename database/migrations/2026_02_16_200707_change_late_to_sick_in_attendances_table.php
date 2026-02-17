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
        Schema::table('attendances', function (Blueprint $table) {
            // Modify the 'status' column to include 'Sick' instead of 'Late'
            $table->enum('status', ['Present', 'Absent', 'Sick', 'Excused'])->default('Present')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Revert changes
            $table->enum('status', ['Present', 'Absent', 'Late', 'Excused'])->default('Present')->change();
        });
    }
};
