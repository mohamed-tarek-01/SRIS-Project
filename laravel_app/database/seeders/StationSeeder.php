<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Station::insert([
            ['name' => 'Cairo-Suez Road Station', 'location' => 'Suez Road, New Cairo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cairo-Alexandria Desert Road', 'location' => 'Alex Desert Road, Giza', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ring Road - Maadi Exit', 'location' => 'Ring Road, Maadi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Regional Ring Road - Giza', 'location' => 'Regional Ring Road, Giza', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
