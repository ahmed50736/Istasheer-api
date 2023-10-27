<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create(['name' => 'test attorney', 'username' => 'testattorney', 'email' => 'attorney@attorney.com', 'password' => bcrypt(123456789), 'user_type' => 2, 'login_type' => 1, 'verified' => 1, 'gender' => 'male', 'phone_no' => null]);
        User::create(['name' => 'attorney 2', 'username' => 'attorney1', 'email' => 'user@user.com', 'password' => bcrypt(123456789), 'user_type' => 3, 'login_type' => 1, 'verified' => 1, 'gender' => 'male', 'phone_no' => null]);
    }
}
