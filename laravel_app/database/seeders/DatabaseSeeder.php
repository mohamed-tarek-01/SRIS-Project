<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(StationSeeder::class);

        // Dummy Traffic Records matching the "Traffic Authority" database
        \App\Models\TrafficRecord::insert([
            ['national_id' => '12345678901234', 'plate_number' => 'ABC 123'],
            ['national_id' => '12345678901234', 'plate_number' => 'XYZ 987'],
            ['national_id' => '98765432109876', 'plate_number' => 'DEF 456'],
            ['national_id' => '11112222333344', 'plate_number' => 'سطن٧١٢٦'],
        ]);

        // Creating an Admin User
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@demo.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        
        // Creating a dummy user who already registered
        User::create([
            'name' => 'Demo User',
            'email' => 'user@demo.com',
            'national_id' => '12345678901234',
            'e_wallet_phone' => '01012345678',
            'password' => bcrypt('password'),
            'role' => 'user',
            'balance' => 150.00,
        ]);

        // Egyptian User with Arabic Plate
        User::create([
            'name' => 'Egyptian Driver',
            'email' => 'driver@demo.com',
            'national_id' => '11112222333344',
            'e_wallet_phone' => '01198765432',
            'password' => bcrypt('password'),
            'role' => 'user',
            'balance' => 250.00,
        ]);
    }
}
