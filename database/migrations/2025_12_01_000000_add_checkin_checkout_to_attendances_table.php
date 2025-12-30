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
        // Check if columns exist before adding
        if (!Schema::hasColumn('attendances', 'checkin_time')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->timestamp('checkin_time')->nullable()->after('attendance_date');
            });
        }
        
        if (!Schema::hasColumn('attendances', 'checkout_time')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->timestamp('checkout_time')->nullable()->after('checkin_time');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Note: Don't drop index as it exists from original migration
            $table->dropColumn(['checkin_time', 'checkout_time']);
        });
    }
};

