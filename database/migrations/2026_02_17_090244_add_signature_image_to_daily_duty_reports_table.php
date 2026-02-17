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
            $table->longText('signature_image')->nullable()->after('signed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_duty_reports', function (Blueprint $table) {
            $table->dropColumn('signature_image');
        });
    }
};
