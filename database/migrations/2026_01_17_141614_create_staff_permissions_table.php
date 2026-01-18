<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('staff_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profession_id'); // link to staff_professions table
            $table->string('name'); // permission name (e.g., 'examination_create', 'fees_view')
            $table->string('guard_name')->default('web');
            $table->string('permission_category')->nullable(); // examination, fees, teacher_management, etc.
            $table->timestamps();

            // Foreign key
            $table->foreign('profession_id')->references('id')->on('staff_professions')->onDelete('cascade');
            
            // Unique constraint: same profession can't have duplicate permission
            $table->unique(['profession_id', 'name'], 'staff_permissions_profession_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_permissions');
    }
};
