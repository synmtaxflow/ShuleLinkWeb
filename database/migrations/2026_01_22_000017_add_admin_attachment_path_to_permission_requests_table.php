<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('permission_requests', 'admin_attachment_path')) {
            Schema::table('permission_requests', function (Blueprint $table) {
                $table->string('admin_attachment_path')->nullable()->after('admin_response');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('permission_requests', 'admin_attachment_path')) {
            Schema::table('permission_requests', function (Blueprint $table) {
                $table->dropColumn('admin_attachment_path');
            });
        }
    }
};
