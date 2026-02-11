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
        Schema::table('fees', function (Blueprint $table) {
            // Remove fee_type column (Tuition/Other separation)
            $table->dropColumn('fee_type');
            
            // Add new fields for priority payment system
            $table->boolean('must_start_pay')->default(false)->after('amount'); // Lazima alipe kabla kusoma
            $table->decimal('payment_deadline_amount', 10, 2)->nullable()->after('must_start_pay'); // Kiasi lazima kabla ya deadline
            $table->date('payment_deadline_date')->nullable()->after('payment_deadline_amount'); // Tarehe ya mwisho
            $table->integer('display_order')->default(0)->after('payment_deadline_date'); // For priority sorting
            
            // Rename fee_name to be more descriptive (if needed)
            // Keep existing: fee_name, amount, description, duration, status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            // Restore fee_type column
            $table->enum('fee_type', ['Tuition Fees', 'Other Fees'])->after('classID');
            
            // Remove new columns
            $table->dropColumn([
                'must_start_pay',
                'payment_deadline_amount',
                'payment_deadline_date',
                'display_order'
            ]);
        });
    }
};
