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
            // SECTION A: Additional student particulars
            $table->string('birth_certificate_number')->nullable()->after('date_of_birth');
            $table->string('religion')->nullable()->after('birth_certificate_number');
            $table->string('nationality')->nullable()->after('religion');

            // SECTION C: Health information
            $table->text('general_health_condition')->nullable()->after('nationality');
            $table->boolean('has_disability')->default(false)->after('general_health_condition');
            $table->text('disability_details')->nullable()->after('has_disability');
            $table->boolean('has_chronic_illness')->default(false)->after('disability_details');
            $table->text('chronic_illness_details')->nullable()->after('has_chronic_illness');
            $table->text('immunization_details')->nullable()->after('chronic_illness_details');

            // SECTION D: Emergency contact
            $table->string('emergency_contact_name')->nullable()->after('immunization_details');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_relationship');

            // SECTION E & F: Declaration and official use
            $table->text('parent_declaration')->nullable()->after('emergency_contact_phone');
            $table->string('parent_signature')->nullable()->after('parent_declaration');
            $table->date('declaration_date')->nullable()->after('parent_signature');
            $table->string('registering_officer_name')->nullable()->after('declaration_date');
            $table->string('registering_officer_title')->nullable()->after('registering_officer_name');
            $table->string('registering_officer_signature')->nullable()->after('registering_officer_title');
            $table->string('school_stamp')->nullable()->after('registering_officer_signature');
            $table->enum('registration_status', ['Draft', 'Submitted', 'Completed'])->default('Draft')->after('school_stamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'birth_certificate_number',
                'religion',
                'nationality',
                'general_health_condition',
                'has_disability',
                'disability_details',
                'has_chronic_illness',
                'chronic_illness_details',
                'immunization_details',
                'emergency_contact_name',
                'emergency_contact_relationship',
                'emergency_contact_phone',
                'parent_declaration',
                'parent_signature',
                'declaration_date',
                'registering_officer_name',
                'registering_officer_title',
                'registering_officer_signature',
                'school_stamp',
                'registration_status'
            ]);
        });
    }
};
