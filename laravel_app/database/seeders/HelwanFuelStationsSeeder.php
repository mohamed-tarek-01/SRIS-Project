<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HelwanFuelStationsSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            [
                'name'      => 'Wataniya Petrol Station — Helwan',
                'type'      => 'fuel',
                'location'  => 'Helwan, Cairo Governorate',
                'price'     => 0,
                'latitude'  => 29.8500,
                'longitude' => 31.3340,
            ],
            [
                'name'      => 'Misr Petroleum — Helwan',
                'type'      => 'fuel',
                'location'  => 'El Helwan St, Helwan',
                'price'     => 0,
                'latitude'  => 29.8420,
                'longitude' => 31.3290,
            ],
            [
                'name'      => 'TotalEnergies — Ain Helwan',
                'type'      => 'fuel',
                'location'  => 'Ain Helwan, Helwan',
                'price'     => 0,
                'latitude'  => 29.8610,
                'longitude' => 31.3390,
            ],
            [
                'name'      => 'Taqa Petrol — Helwan Corniche',
                'type'      => 'fuel',
                'location'  => 'Corniche El Nil, Helwan',
                'price'     => 0,
                'latitude'  => 29.8560,
                'longitude' => 31.3350,
            ],
            [
                'name'      => 'Cairo Oil — Tibin',
                'type'      => 'fuel',
                'location'  => 'Tibin Industrial Zone, Helwan',
                'price'     => 0,
                'latitude'  => 29.8700,
                'longitude' => 31.3400,
            ],
            [
                'name'      => 'Egypt Petrol — El-Masaken',
                'type'      => 'fuel',
                'location'  => 'El-Masaken El-Eqtisadiya, Helwan',
                'price'     => 0,
                'latitude'  => 29.8330,
                'longitude' => 31.3250,
            ],
            [
                'name'      => 'Sila Petrol Station — Helwan',
                'type'      => 'fuel',
                'location'  => 'El Sharq Rd, Helwan',
                'price'     => 0,
                'latitude'  => 29.8450,
                'longitude' => 31.3310,
            ],
        ];

        foreach ($stations as $station) {
            // Avoid duplicates on re-run
            DB::table('stations')->updateOrInsert(
                ['name' => $station['name']],
                array_merge($station, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
