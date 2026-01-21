<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_records', function (Blueprint $table) {
            $table->bigIncrements('expense_recordID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('expense_budgetID');
            $table->date('expense_date');
            $table->decimal('amount', 12, 2);
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_records');
    }
};
