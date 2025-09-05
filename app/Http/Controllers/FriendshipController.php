<?php

namespace App\Http\Controllers;

use App\Events\FriendRequestAccepted;
use App\Events\FriendRequestSent;
use App\Models\Friendship;
use App\Models\User;
use App\Notifications\FriendRequestNotification;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    // إرسال طلب صداقة
    public function sendRequest($friend_id)
    {
        $userId = auth()->id();
    
        // لا تبعت لنفسك
        if ($userId == $friend_id) {
            return response()->json(['message' => 'لا يمكنك إرسال طلب لنفسك.'], 400);
        }
    
        // تحقق من وجود صداقة أو طلب سابق بأي اتجاه
        $exists = Friendship::where(function ($q) use ($userId, $friend_id) {
            $q->where('user_id', $userId)->where('friend_id', $friend_id);
        })->orWhere(function ($q) use ($userId, $friend_id) {
            $q->where('user_id', $friend_id)->where('friend_id', $userId);
        })->exists();
    
        if ($exists) {
            return response()->json(['message' => 'تم إرسال الطلب مسبقاً أو أنت بالفعل صديق.'], 400);
        }
    
        Friendship::create([
            'user_id' => $userId,
            'friend_id' => $friend_id,
            'status' => 'pending',
        ]);
    
        event(new FriendRequestSent(auth()->user()));
    
        return response()->json(['message' => 'تم إرسال طلب الصداقة.']);
    }
    
    // قبول طلب صداقة
    public function acceptRequest($user_id)
    {
        $friendship = Friendship::where('user_id', $user_id)
            ->where('friend_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->update(['status' => 'accepted']);
        event(new FriendRequestAccepted(auth()->user(), $user_id));

        return response()->json(['message' => 'تم قبول طلب الصداقة.']);
    }

    // رفض طلب صداقة
    public function declineRequest($user_id)
    {
        $deleted = Friendship::where('user_id', $user_id)
            ->where('friend_id', auth()->id())
            ->where('status', 'pending')
            ->delete();

        return response()->json(['message' => $deleted ? 'تم رفض الطلب.' : 'الطلب غير موجود.']);
    }

    // إلغاء طلب الصداقة (قبل ما ينقبل)
    public function cancelRequest($friend_id)
    {
        $deleted = Friendship::where('user_id', auth()->id())
            ->where('friend_id', $friend_id)
            ->where('status', 'pending')
            ->delete();

        return response()->json(['message' => $deleted ? 'تم إلغاء الطلب.' : 'لم يتم العثور على الطلب.']);
    }

    // حذف صديق
    public function unfriend($friend_id)
    {
        $deleted = Friendship::where(function ($q) use ($friend_id) {
            $q->where('user_id', auth()->id())->where('friend_id', $friend_id);
        })->orWhere(function ($q) use ($friend_id) {
            $q->where('user_id', $friend_id)->where('friend_id', auth()->id());
        })->where('status', 'accepted')->delete();

        return response()->json(['message' => $deleted ? 'تم حذف الصديق.' : 'الصداقة غير موجودة.']);
    }

    // عرض قائمة الأصدقاء
    public function friends(Request $request)
    {
        $user = $request->user();

        // أصدقاء الطرفين (سواء أنا user_id أو friend_id)
        $friends = Friendship::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('friend_id', $user->id);
            })
            ->where('status', 'accepted')
            ->with(['user.profileImage', 'friend.profileImage'])
            ->get()
            ->map(function ($friendship) use ($user) {
                $friend = $friendship->user_id == $user->id
                    ? $friendship->friend
                    : $friendship->user;

                return [
                    "id" => $friend->id,
                    "name" => $friend->name,
                    "email" => $friend->email,
                    "profile_image" => $friend->profileImage
                        ? ["url" => $friend->profileImage->url]
                        : null,
                ];
            });

        return response()->json($friends);
    }

    // عرض الطلبات الواردة (اللي جاي لعندك)
    public function incomingRequests()
    {
        $requests = Friendship::where('friend_id', auth()->id())
            ->where('status', 'pending')
            ->with('user:id,name') // عرض معلومات المرسل
            ->get();

        return response()->json($requests);
    }

    // عرض الطلبات المرسلة (اللي أنت أرسلتها)
    public function sentRequests()
    {
        $requests = Friendship::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->with('friend:id,name')
            ->get();

        return response()->json($requests);
    }
}
