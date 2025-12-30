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
            $table->timestamp('check_in_time')->nullable()->after('punch_time');
            $table->timestamp('check_out_time')->nullable()->after('check_in_time');
            $table->date('attendance_date')->nullable()->after('check_out_time');
            
            // Add index for faster lookups by user and date
            $table->index(['user_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'attendance_date']);
            $table->dropColumn(['check_in_time', 'check_out_time', 'attendance_date']);
        });
    }
};
