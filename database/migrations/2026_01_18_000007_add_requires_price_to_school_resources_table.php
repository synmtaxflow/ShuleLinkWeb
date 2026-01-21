<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_resources', function (Blueprint $table) {
            if (!Schema::hasColumn('school_resources', 'requires_price')) {
                $table->boolean('requires_price')->default(true)->after('requires_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('school_resources', function (Blueprint $table) {
            if (Schema::hasColumn('school_resources', 'requires_price')) {
                $table->dropColumn('requires_price');
            }
        });
    }
};
