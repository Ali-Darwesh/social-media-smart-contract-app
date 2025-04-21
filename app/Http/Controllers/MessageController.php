<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;

class MessageController extends Controller
{
    public function index($chatId)
    {
        $chat = Chat::findOrFail($chatId);

      //  $this->authorize('view', $chat); // optional: لو عامل policies

        $messages = $chat->messages()->with('sender')->orderBy('created_at')->get();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'receiver_id' => 'required|exists:users,id',
        ]);

        $sender = Auth::user();
        $receiverId = $request->receiver_id;

        // ابحث عن شات مشترك أو أنشئ واحد جديد
        $chat = Chat::firstOrCreate([
            'user_one_id' => min($sender->id, $receiverId),
            'user_two_id' => max($sender->id, $receiverId),
        ]);

        // أنشئ الرسالة
        $message = Message::create([
            'chat_id'    => $chat->id,
            'sender_id'  => $sender->id,
            'receiver_id'=> $receiverId,
            'content'    => $request->content,
        ]);

        broadcast(new MessageSent($message->load('sender')))->toOthers();

        return response()->json(['message' => $message->load('sender')], 201);
    }
}
