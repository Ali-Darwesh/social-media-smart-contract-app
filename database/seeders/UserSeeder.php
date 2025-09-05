<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'zein',
                'email' => 'z@gmail.com',
                'age' => '25',
                'gender' => 'male',
                'password' => Hash::make('123456'),
            ],
            [
                'name' => 'ali',
                'email' => 'a@gmail.com',
                'age' => '25',
                'gender' => 'male',
                'password' => Hash::make('123456'),
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $faker = Faker::create();

        foreach (range(1, 50) as $i) {
            User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'age' => $faker->numberBetween(18, 100),
                'gender' => $faker->randomElement(['male', 'female']),
                'is_banned' => $faker->boolean(10), // 10% احتمالية يكون محظور
                'email_verified_at' => $faker->optional()->dateTime,
                'password' => Hash::make('password123'), // كلمة مرور افتراضية
                'remember_token' => Str::random(10),
            ]);
        }
    }
}
