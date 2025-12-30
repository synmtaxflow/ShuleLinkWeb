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
        Schema::table('users', function (Blueprint $table) {
            $table->string('enroll_id')->nullable()->unique()->after('id'); // Device enrollment ID
            $table->boolean('registered_on_device')->default(false)->after('enroll_id');
            $table->timestamp('device_registered_at')->nullable()->after('registered_on_device');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['enroll_id', 'registered_on_device', 'device_registered_at']);
        });
    }
};
