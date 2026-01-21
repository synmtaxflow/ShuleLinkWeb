<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outgoing_resource_records', function (Blueprint $table) {
            $table->integer('returned_quantity')->default(0)->after('returned_at');
        });
    }

    public function down(): void
    {
        Schema::table('outgoing_resource_records', function (Blueprint $table) {
            $table->dropColumn('returned_quantity');
        });
    }
};
