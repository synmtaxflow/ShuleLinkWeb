<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_records', function (Blueprint $table) {
            $table->bigIncrements('revenue_recordID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('revenue_sourceID');
            $table->date('record_date');
            $table->decimal('unit_amount', 12, 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_records');
    }
};
