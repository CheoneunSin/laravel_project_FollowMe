<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserTableAdminSeeder::class);
        $this->call(PatientTableSeeder::class);
        $this->call(ClinicTableSeeder::class);

        $this->call(NodeTableSeeder::class);
        $this->call(NodeDistanceTableSeeder::class);
        $this->call(RoomLocationsSeeder::class);
        
        $this->call(BeaconTableSeeder::class);
        
    }
}
