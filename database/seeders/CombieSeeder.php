<?php

namespace Database\Seeders;

use App\Models\Combie;
use App\Models\School;
use Illuminate\Database\Seeder;

class CombieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all schools
        $schools = School::all();

        // Define all combinations
        $combies = [
            // Basic Combinations
            [
                'combie_name' => 'Science',
                'combie_code' => 'SCI',
                'description' => 'Science combination for students pursuing science subjects',
            ],
            [
                'combie_name' => 'Arts',
                'combie_code' => 'ART',
                'description' => 'Arts combination for students pursuing arts subjects',
            ],
            [
                'combie_name' => 'Business',
                'combie_code' => 'BUS',
                'description' => 'Business combination for students pursuing business subjects',
            ],
            // Advanced Level Combinations
            [
                'combie_name' => 'PCM',
                'combie_code' => 'PCM',
                'description' => 'Physics, Chemistry, Mathematics combination',
            ],
            [
                'combie_name' => 'PGM',
                'combie_code' => 'PGM',
                'description' => 'Physics, Geography, Mathematics combination',
            ],
            [
                'combie_name' => 'HKL',
                'combie_code' => 'HKL',
                'description' => 'History, Kiswahili, Literature combination',
            ],
            [
                'combie_name' => 'HGE',
                'combie_code' => 'HGE',
                'description' => 'History, Geography, Economics combination',
            ],
            [
                'combie_name' => 'PCB',
                'combie_code' => 'PCB',
                'description' => 'Physics, Chemistry, Biology combination',
            ],
            [
                'combie_name' => 'HGL',
                'combie_code' => 'HGL',
                'description' => 'History, Geography, Literature combination',
            ],
            [
                'combie_name' => 'HGK',
                'combie_code' => 'HGK',
                'description' => 'History, Geography, Kiswahili combination',
            ],
            [
                'combie_name' => 'CBG',
                'combie_code' => 'CBG',
                'description' => 'Chemistry, Biology, Geography combination',
            ],
            [
                'combie_name' => 'CBA',
                'combie_code' => 'CBA',
                'description' => 'Chemistry, Biology, Agriculture combination',
            ],
            [
                'combie_name' => 'EGM',
                'combie_code' => 'EGM',
                'description' => 'Economics, Geography, Mathematics combination',
            ],
            [
                'combie_name' => 'EKN',
                'combie_code' => 'EKN',
                'description' => 'Economics, Commerce, Accounts combination',
            ],
            [
                'combie_name' => 'DST',
                'combie_code' => 'DST',
                'description' => 'Divinity, Swahili, Literature combination',
            ],
            [
                'combie_name' => 'HGEB',
                'combie_code' => 'HGEB',
                'description' => 'History, Geography, Economics, Bible Knowledge combination',
            ],
            [
                'combie_name' => 'HGLB',
                'combie_code' => 'HGLB',
                'description' => 'History, Geography, Literature, Bible Knowledge combination',
            ],
            [
                'combie_name' => 'PCMB',
                'combie_code' => 'PCMB',
                'description' => 'Physics, Chemistry, Mathematics, Biology combination',
            ],
            [
                'combie_name' => 'PCBG',
                'combie_code' => 'PCBG',
                'description' => 'Physics, Chemistry, Biology, Geography combination',
            ],
        ];

        // Insert combies for each school
        foreach ($schools as $school) {
            foreach ($combies as $combieData) {
                // Check if combie already exists for this school
                $existingCombie = Combie::where('schoolID', $school->schoolID)
                    ->where('combie_name', $combieData['combie_name'])
                    ->first();

                if (!$existingCombie) {
                    Combie::create([
                        'schoolID' => $school->schoolID,
                        'combie_name' => $combieData['combie_name'],
                        'combie_code' => $combieData['combie_code'],
                        'description' => $combieData['description'],
                        'status' => 'Active',
                    ]);
                }
            }
        }

        $this->command->info('Combies seeded successfully for all schools!');
    }
}
