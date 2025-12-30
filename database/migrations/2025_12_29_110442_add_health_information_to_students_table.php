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
        Schema::table('students', function (Blueprint $table) {
            $table->boolean('is_disabled')->default(false)->after('photo');
            $table->boolean('has_epilepsy')->default(false)->after('is_disabled');
            $table->boolean('has_allergies')->default(false)->after('has_epilepsy');
            $table->text('allergies_details')->nullable()->after('has_allergies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['is_disabled', 'has_epilepsy', 'has_allergies', 'allergies_details']);
        });
    }
};
