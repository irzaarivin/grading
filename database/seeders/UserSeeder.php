<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'	=> "Amba Project Manager",
            'role' => "project-manager",
            'email'	=> 'pm@codepolitan.com',
            'password'	=> bcrypt('secret'),
            'jk' => "pria",
        ]);

        User::create([
            'name'	=> "Amba Developer",
            'role' => "developer",
            'email'	=> 'developer@codepolitan.com',
            'password'	=> bcrypt('secret'),
            'jk' => "wanita",
        ]);

        User::create([
            'name'	=> "Amba Tester",
            'role' => "tester",
            'email'	=> 'tester@codepolitan.com',
            'password'	=> bcrypt('secret'),
            'jk' => "pria",
        ]);
    }
}
