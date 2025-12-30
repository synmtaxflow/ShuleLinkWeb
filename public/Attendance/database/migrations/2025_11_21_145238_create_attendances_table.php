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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('enroll_id'); // User ID on device (PIN)
            $table->timestamp('punch_time'); // DateTime of punch
            $table->string('status')->nullable(); // IN/OUT, etc if you use it
            $table->string('verify_mode')->nullable(); // fingerprint/face/card
            $table->string('device_ip')->nullable();
            $table->timestamps();

            $table->index(['enroll_id', 'punch_time']);
            $table->index(['user_id', 'punch_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
