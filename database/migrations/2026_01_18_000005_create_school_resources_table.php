<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_resources', function (Blueprint $table) {
            $table->bigIncrements('resourceID');
            $table->unsignedBigInteger('schoolID');
            $table->string('resource_name');
            $table->string('resource_type');
            $table->boolean('requires_quantity')->default(true);
            $table->integer('quantity')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_resources');
    }
};
