<?php

use Illuminate\Database\Seeder;
use App\User;
class UserTableAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'followme@gmail.com',
            'password' => bcrypt('followme'),
            'role' => 1
        ]);
    }
}
