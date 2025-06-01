<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
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
            Admin::create($admin);
        }
    }
}
