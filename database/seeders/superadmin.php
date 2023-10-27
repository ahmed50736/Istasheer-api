<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class superadmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data=['name'=>'admin','username' => 'superadmin','email'=>'admin@admin.com','password'=>bcrypt(123456789), 'phone_no' => '96592220333', 'user_type'=>1,'login_type'=>1,'verified'=>1,'gender'=>'male'];
        User::create($data);
    }
}
