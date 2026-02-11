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
        Schema::table('student_fee_payments', function (Blueprint $table) {
            $table->foreignId('paymentID')->after('studentID')->nullable()->constrained('payments', 'paymentID')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fee_payments', function (Blueprint $table) {
            $table->dropForeign(['paymentID']);
            $table->dropColumn('paymentID');
        });
    }
};
