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
        Schema::table('examinations', function (Blueprint $table) {
            if (!Schema::hasColumn('examinations', 'enter_result')) {
                $table->boolean('enter_result')->default(false)->after('approval_status')->comment('Allow teachers to enter results');
            }
            if (!Schema::hasColumn('examinations', 'publish_result')) {
                $table->boolean('publish_result')->default(false)->after('enter_result')->comment('Publish results for public view');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examinations', function (Blueprint $table) {
            $table->dropColumn(['enter_result', 'publish_result']);
        });
    }
};
