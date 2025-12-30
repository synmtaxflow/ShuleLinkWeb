<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'Applied' status to the enum
        DB::statement("ALTER TABLE students MODIFY COLUMN status ENUM('Active', 'Transferred', 'Graduated', 'Inactive', 'Applied') DEFAULT 'Active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'Applied' status from the enum (revert to original)
        DB::statement("ALTER TABLE students MODIFY COLUMN status ENUM('Active', 'Transferred', 'Graduated', 'Inactive') DEFAULT 'Active'");
    }
};
