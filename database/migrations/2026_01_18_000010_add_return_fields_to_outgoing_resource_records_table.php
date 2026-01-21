<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outgoing_resource_records', function (Blueprint $table) {
            $table->boolean('is_returned')->default(false)->after('outgoing_type');
            $table->date('returned_at')->nullable()->after('is_returned');
            $table->text('return_description')->nullable()->after('returned_at');
        });
    }

    public function down(): void
    {
        Schema::table('outgoing_resource_records', function (Blueprint $table) {
            $table->dropColumn(['is_returned', 'returned_at', 'return_description']);
        });
    }
};
