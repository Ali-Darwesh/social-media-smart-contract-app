<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\PostReaction;

class PostReactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $posts = Post::all();

        foreach ($posts as $post) {
            // عشوائي كم عدد المستخدمين رح يعطوا تفاعل لهذا البوست
            $reactingUsers = $users->random(rand(5, 15));

            foreach ($reactingUsers as $user) {
                PostReaction::updateOrCreate(
                    [
                        'post_id' => $post->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'type' => rand(0, 1) ? 'like' : 'dislike',
                    ]
                );
            }
        }
    }
}
