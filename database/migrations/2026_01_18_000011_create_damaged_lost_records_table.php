<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damaged_lost_records', function (Blueprint $table) {
            $table->bigIncrements('damaged_lostID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('resourceID');
            $table->date('record_date');
            $table->string('record_type', 20);
            $table->integer('quantity')->nullable();
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damaged_lost_records');
    }
};
