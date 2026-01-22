<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_visitors')) {
            Schema::create('school_visitors', function (Blueprint $table) {
                $table->bigIncrements('visitorID');
                $table->unsignedBigInteger('schoolID');
                $table->date('visit_date');
                $table->string('name');
                $table->string('contact')->nullable();
                $table->string('occupation')->nullable();
                $table->string('reason')->nullable();
                $table->longText('signature')->nullable();
                $table->timestamps();

                $table->index(['schoolID', 'visit_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_visitors');
    }
};
