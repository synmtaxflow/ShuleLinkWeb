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
        // Modify enum to include 'Excused'
        DB::statement("ALTER TABLE `exam_attendance` MODIFY COLUMN `status` ENUM('Present', 'Absent', 'Excused') NOT NULL DEFAULT 'Absent'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum (remove 'Excused')
        DB::statement("ALTER TABLE `exam_attendance` MODIFY COLUMN `status` ENUM('Present', 'Absent') NOT NULL DEFAULT 'Absent'");
    }
};
