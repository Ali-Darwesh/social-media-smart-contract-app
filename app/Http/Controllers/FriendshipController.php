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
        if (Friendship::where('user_id', auth()->id())->where('friend_id', $friend_id)->exists()) {
            return response()->json(['message' => 'تم إرسال الطلب مسبقاً أو أنت بالفعل صديق.'], 400);
        }

        Friendship::create([
            'user_id' => auth()->id(),
            'friend_id' => $friend_id,
            'status' => 'pending',
        ]);
        $toUser = User::findOrFail($friend_id);
        event(new FriendRequestSent(auth()->user(), $toUser));

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
    public function friends()
    {
        $friends = Friendship::where(function ($q) {
            $q->where('user_id', auth()->id())
                ->orWhere('friend_id', auth()->id());
        })->where('status', 'accepted')->get();

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
