<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Db;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'Admin@abv.bg',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
            'remember_token' => Str::random(10),
            'created_at' => now()
        ]);

        // \App\Models\User::factory(5)->create();
    }
}
