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
            $table->unsignedBigInteger('sponsor_id')->nullable()->after('parentID');
            $table->decimal('sponsorship_percentage', 5, 2)->default(0.00)->after('sponsor_id');

            $table->foreign('sponsor_id')->references('sponsorID')->on('sponsors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['sponsor_id']);
            $table->dropColumn(['sponsor_id', 'sponsorship_percentage']);
        });
    }
};
