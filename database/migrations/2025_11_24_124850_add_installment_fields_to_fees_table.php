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
            $table->boolean('allow_installments')->default(false)->after('duration');
            $table->enum('default_installment_type', ['Semester', 'Month', 'Two Months', 'Term', 'Quarter', 'One-time'])->nullable()->after('allow_installments');
            $table->integer('number_of_installments')->nullable()->after('default_installment_type'); // e.g., 2 for 2 semesters, 12 for 12 months
            $table->boolean('allow_partial_payment')->default(true)->after('number_of_installments'); // If false, parent must pay full amount
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropColumn(['allow_installments', 'default_installment_type', 'number_of_installments', 'allow_partial_payment']);
        });
    }
};
