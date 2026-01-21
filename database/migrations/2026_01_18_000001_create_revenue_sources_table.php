<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_sources', function (Blueprint $table) {
            $table->bigIncrements('revenue_sourceID');
            $table->unsignedBigInteger('schoolID');
            $table->string('source_name');
            $table->enum('source_type', ['fixed', 'per_item', 'variable'])->default('fixed');
            $table->decimal('default_amount', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_sources');
    }
};
