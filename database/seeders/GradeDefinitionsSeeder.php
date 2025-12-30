<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradeDefinitionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // O-Level Grading System (Form 1-4): 75-100=A, 65-74=B, 45-64=C, 30-44=D, 0-29=F
        $oLevelGrades = [
            ['first' => 75.00, 'last' => 100.00, 'grade' => 'A'],
            ['first' => 65.00, 'last' => 74.00, 'grade' => 'B'],
            ['first' => 45.00, 'last' => 64.00, 'grade' => 'C'],
            ['first' => 30.00, 'last' => 44.00, 'grade' => 'D'],
            ['first' => 0.00, 'last' => 29.00, 'grade' => 'F'],
        ];

        // A-Level Grading System (Form 5-6): >=80=A, >=70=B, >=60=C, >=50=D, >=40=E, <40=S/F
        $aLevelGrades = [
            ['first' => 80.00, 'last' => 100.00, 'grade' => 'A'],
            ['first' => 70.00, 'last' => 79.00, 'grade' => 'B'],
            ['first' => 60.00, 'last' => 69.00, 'grade' => 'C'],
            ['first' => 50.00, 'last' => 59.00, 'grade' => 'D'],
            ['first' => 40.00, 'last' => 49.00, 'grade' => 'E'],
            ['first' => 0.00, 'last' => 39.00, 'grade' => 'S/F'],
        ];

        // O-Level Classes: FORM ONE (6), FORM TWO (15), FORM THREE (13), FORM FOUR (14)
        $oLevelClassIDs = [6, 15, 13, 14];
        
        // A-Level Classes: FORM FIVE (10), FORM SIX (11)
        $aLevelClassIDs = [10, 11];

        // Insert O-Level grade definitions
        foreach ($oLevelClassIDs as $classID) {
            foreach ($oLevelGrades as $gradeDef) {
                DB::table('grade_definitions')->updateOrInsert(
                    [
                        'classID' => $classID,
                        'grade' => $gradeDef['grade']
                    ],
                    [
                        'first' => $gradeDef['first'],
                        'last' => $gradeDef['last'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        // Insert A-Level grade definitions
        foreach ($aLevelClassIDs as $classID) {
            foreach ($aLevelGrades as $gradeDef) {
                DB::table('grade_definitions')->updateOrInsert(
                    [
                        'classID' => $classID,
                        'grade' => $gradeDef['grade']
                    ],
                    [
                        'first' => $gradeDef['first'],
                        'last' => $gradeDef['last'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        $this->command->info('Grade definitions inserted successfully!');
        $this->command->info('O-Level classes: ' . implode(', ', $oLevelClassIDs));
        $this->command->info('A-Level classes: ' . implode(', ', $aLevelClassIDs));
    }
}











