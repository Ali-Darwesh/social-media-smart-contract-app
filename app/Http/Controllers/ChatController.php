<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
 public function getOrCreate(Request $request)
{
    $userId = Auth::id();
    $friendId = $request->query('friend_id');

    if (!$friendId) {
        return response()->json(['error' => 'friend_id is required'], 422);
    }

    $chat = Chat::where(function ($q) use ($userId, $friendId) {
                    $q->where('user_one_id', $userId)->where('user_two_id', $friendId);
                })
                ->orWhere(function ($q) use ($userId, $friendId) {
                    $q->where('user_one_id', $friendId)->where('user_two_id', $userId);
                })
                ->first();

    if (!$chat) {
        $chat = Chat::create([
            'user_one_id' => $userId,
            'user_two_id' => $friendId,
        ]);
    }

    return response()->json(['chat' => $chat]);
}

    public function index()
    {
        $userId = Auth::id();

        $chats = Chat::where('user_one_id', $userId)
                     ->orWhere('user_two_id', $userId)
                     ->with(['messages' => function ($query) {
                         $query->latest()->limit(1);
                     }, 'userOne', 'userTwo'])
                     ->get();

        return response()->json($chats);
    }

    public function show($id)
    {
        $chat = Chat::with(['messages.sender'])->findOrFail($id);

        // optional: تحقق من صلاحية الدخول للشات
        if (!in_array(Auth::id(), [$chat->user_one_id, $chat->user_two_id])) {
            abort(403, 'Unauthorized');
        }

        return response()->json($chat);
    }
}
