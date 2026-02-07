<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_damages', function (Blueprint $table) {
            if (!Schema::hasColumn('book_damages', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->after('status');
            }
            if (!Schema::hasColumn('book_damages', 'payment_method')) {
                $table->enum('payment_method', ['replace', 'cash'])->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('book_damages', 'payment_amount')) {
                $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('book_damages', function (Blueprint $table) {
            if (Schema::hasColumn('book_damages', 'payment_amount')) {
                $table->dropColumn('payment_amount');
            }
            if (Schema::hasColumn('book_damages', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('book_damages', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
