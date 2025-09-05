<?php
namespace App\Services;

use App\Models\Friendship;
use App\Models\User;

class UserService
{
    public function getFullProfile($id = null)
    {
        $authId = auth()->id();
        $targetId = $id ?? $authId; // إذا ما أعطيت id خذ id تبعك

        $user = User::with([
            'profileImage',
            'posts.images',
            'posts.videos',
            'posts.comments.author',
            'posts.reactions.user',
            'contracts'
        ])->findOrFail($targetId);

        // رجّع كمان قائمة الأصدقاء
        $friends = Friendship::where(function ($q) use ($targetId) {
                $q->where('user_id', $targetId)
                  ->orWhere('friend_id', $targetId);
            })
            ->where('status', 'accepted')
            ->with(['user.profileImage', 'friend.profileImage'])
            ->get()
            ->map(function ($friendship) use ($targetId) {
                if ($friendship->user_id == $targetId) {
                    return $friendship->friend;
                }
                return $friendship->user;
            });

        return [
            'user' => $user,
            'friends' => $friends->values(),
        ];
    }
}
