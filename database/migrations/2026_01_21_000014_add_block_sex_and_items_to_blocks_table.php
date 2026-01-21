<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->enum('block_sex', ['Male', 'Female', 'Mixed'])->nullable()->after('block_type');
            $table->json('block_items')->nullable()->after('block_sex');
        });
    }

    public function down(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->dropColumn(['block_sex', 'block_items']);
        });
    }
};
