<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Shreeji Ratnam',
                'email' => 'shreejiratnam4246@gmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('Pramukh$4246'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Nityanand Jha',
                'email' => 'gopalhingu123@gmail.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('Hingu@1234567'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
