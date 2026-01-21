<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_budgets', function (Blueprint $table) {
            $table->bigIncrements('expense_budgetID');
            $table->unsignedBigInteger('schoolID');
            $table->integer('year');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('remaining_amount', 12, 2);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->unique(['schoolID', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_budgets');
    }
};
