<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outgoing_resource_records', function (Blueprint $table) {
            $table->bigIncrements('outgoing_resourceID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('resourceID');
            $table->date('outgoing_date');
            $table->integer('quantity')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outgoing_resource_records');
    }
};
