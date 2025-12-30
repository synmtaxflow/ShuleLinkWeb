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
        if (!Schema::hasColumn('roles', 'schoolID')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unsignedBigInteger('schoolID')->nullable()->after('guard_name');
            });

            // Add foreign key separately to avoid issues
            Schema::table('roles', function (Blueprint $table) {
                $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['schoolID']);
            $table->dropColumn('schoolID');
        });
    }
};
