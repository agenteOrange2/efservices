<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\Driver\LicenseEndorsement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LicenseEndorsementSeeder extends Seeder
{
    public function run(): void
    {
        $endorsements = [
            [
                'code' => 'N',
                'name' => 'Tank',
                'description' => 'Tank vehicles endorsement'
            ],
            [
                'code' => 'S',
                'name' => 'School Bus',
                'description' => 'School bus endorsement'
            ],
            [
                'code' => 'H',
                'name' => 'HAZMAT',
                'description' => 'Hazardous materials endorsement'
            ],
            [
                'code' => 'X',
                'name' => 'Combo',
                'description' => 'Combination of tank vehicle and hazardous materials endorsement'
            ],
            [
                'code' => 'T',
                'name' => 'Double/Triple',
                'description' => 'Double and triple trailers endorsement'
            ],
            [
                'code' => 'P',
                'name' => 'Passenger',
                'description' => 'Passenger transport endorsement'
            ]
        ];

        foreach ($endorsements as $endorsement) {
            LicenseEndorsement::updateOrCreate(
                ['code' => $endorsement['code']],
                $endorsement
            );
        }
    }
}