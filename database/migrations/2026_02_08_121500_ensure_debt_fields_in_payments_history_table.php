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
        Schema::table('payments_history', function (Blueprint $table) {
            // Add debt tracking (from previous years)
            if (!Schema::hasColumn('payments_history', 'debt')) {
                $table->decimal('debt', 10, 2)->default(0)->after('balance');
            }
            
            // Add required fees tracking
            if (!Schema::hasColumn('payments_history', 'required_fees_amount')) {
                $table->decimal('required_fees_amount', 10, 2)->default(0)->after('amount_required');
            }
            
            if (!Schema::hasColumn('payments_history', 'required_fees_paid')) {
                $table->decimal('required_fees_paid', 10, 2)->default(0)->after('required_fees_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments_history', function (Blueprint $table) {
            if (Schema::hasColumn('payments_history', 'debt')) {
                $table->dropColumn('debt');
            }
            if (Schema::hasColumn('payments_history', 'required_fees_amount')) {
                $table->dropColumn('required_fees_amount');
            }
            if (Schema::hasColumn('payments_history', 'required_fees_paid')) {
                $table->dropColumn('required_fees_paid');
            }
        });
    }
};
