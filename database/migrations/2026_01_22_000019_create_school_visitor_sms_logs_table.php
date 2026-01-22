<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_visitor_sms_logs')) {
            Schema::create('school_visitor_sms_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('schoolID');
                $table->text('message');
                $table->unsignedInteger('recipient_count')->default(0);
                $table->json('recipient_ids')->nullable();
                $table->timestamps();

                $table->index(['schoolID', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_visitor_sms_logs');
    }
};
