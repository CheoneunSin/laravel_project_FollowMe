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
        // $this->call(UsersTableSeeder::class);
        $this->call(NodeTableSeeder::class);
        $this->call(NodeDistanceSeeder::class);
        $this->call(BeaconTableSeeder::class);
        
    }
}
