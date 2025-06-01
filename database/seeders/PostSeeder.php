<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Image;
use App\Models\Video;
use App\Models\Comment;
use Faker\Factory as Faker;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $userIds = User::pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command->warn('⚠️ لا يوجد مستخدمون. الرجاء تشغيل UserSeeder أولاً.');
            return;
        }

        foreach (range(1, 50) as $i) {
            // إنشاء البوست
            $post = Post::create([
                'content' => $faker->sentence(10),
                'details' => $faker->paragraph(3),
                'author_id' => $faker->randomElement($userIds),
            ]);

            // ربط صور
            foreach (range(1, rand(1, 3)) as $j) {
                $post->images()->create([
                    'url' => $faker->imageUrl(800, 600, 'nature', true),
                ]);
            }

            // ربط فيديوهات
            foreach (range(1, rand(1, 2)) as $k) {
                $post->videos()->create([
                    'url' => 'https://www.youtube.com/watch?v=' . $faker->regexify('[A-Za-z0-9_-]{11}'),
                ]);
            }

            // ربط تعليقات
            foreach (range(1, rand(2, 5)) as $c) {
                $post->comments()->create([
                    'user_id' => $faker->randomElement($userIds),
                    'body' => $faker->sentence(12),
                ]);
            }
        }

        $this->command->info('✅ تم إنشاء 50 منشور مع صور، فيديوهات، وتعليقات بنجاح.');
    }
}
