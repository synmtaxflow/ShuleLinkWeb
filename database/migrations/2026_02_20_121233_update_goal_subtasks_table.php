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
        Schema::table('goal_subtasks', function (Blueprint $table) {
            $table->decimal('marks', 5, 2)->default(0)->after('weight');
        });

        Schema::create('goal_subtask_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subtask_id');
            $table->date('date');
            $table->text('step_description');
            $table->timestamps();

            $table->foreign('subtask_id')->references('id')->on('goal_subtasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_subtask_steps');
        Schema::table('goal_subtasks', function (Blueprint $table) {
            $table->dropColumn('marks');
        });
    }
};
