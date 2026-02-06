<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE permission_requests MODIFY requester_type ENUM('teacher','student','staff') NOT NULL DEFAULT 'teacher'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE permission_requests MODIFY requester_type ENUM('teacher','student') NOT NULL DEFAULT 'teacher'");
    }
};
