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
        Schema::table('classes', function (Blueprint $table) {
            $table->enum('status', ['Active', 'Inactive'])->default('Inactive')->after('description');
        });

        Schema::table('subclasses', function (Blueprint $table) {
            $table->enum('status', ['Active', 'Inactive'])->default('Inactive')->after('stream_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('subclasses', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
