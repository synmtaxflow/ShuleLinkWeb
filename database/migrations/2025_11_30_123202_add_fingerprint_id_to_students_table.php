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
        Schema::table('students', function (Blueprint $table) {
            $table->string('fingerprint_id', 50)->nullable()->unique()->after('admission_number');
            $table->boolean('sent_to_device')->default(false)->after('fingerprint_id');
            $table->timestamp('device_sent_at')->nullable()->after('sent_to_device');
            $table->integer('fingerprint_capture_count')->default(0)->after('device_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['fingerprint_id', 'sent_to_device', 'device_sent_at', 'fingerprint_capture_count']);
        });
    }
};
