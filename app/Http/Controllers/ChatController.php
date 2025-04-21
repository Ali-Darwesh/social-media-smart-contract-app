<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
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
