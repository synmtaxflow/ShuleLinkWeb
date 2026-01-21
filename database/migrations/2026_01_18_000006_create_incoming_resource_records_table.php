<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_resource_records', function (Blueprint $table) {
            $table->bigIncrements('incoming_resourceID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('resourceID');
            $table->date('received_date');
            $table->integer('quantity')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_resource_records');
    }
};
