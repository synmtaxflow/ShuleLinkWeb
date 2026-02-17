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
        Schema::table('daily_duty_reports', function (Blueprint $table) {
            $table->string('signed_by')->nullable()->after('admin_comments');
            $table->timestamp('signed_at')->nullable()->after('signed_by');
            $table->unsignedBigInteger('approved_by_id')->nullable()->after('signed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_duty_reports', function (Blueprint $table) {
            $table->dropColumn(['signed_by', 'signed_at', 'approved_by_id']);
        });
    }
};
