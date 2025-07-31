<?php

namespace Database\Seeders;

use App\Models\Supervisor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SupervisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'zein',
                'email' => 'z@gmail.com',
                'password' => Hash::make('123456'),
            ],
            [
                'name' => 'ali',
                'email' => 'a@gmail.com',
                'password' => Hash::make('123456'),
            ]
        ];

        foreach ($admins as $admin) {
            Supervisor::create($admin);
        }
    }
}
