<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Cache;
class MessageController extends Controller
{
    public function index($chatId)
    {
        $chat = Chat::findOrFail($chatId);
    
        // تحقق من صلاحية الدخول
        if (!in_array(Auth::id(), [$chat->user_one_id, $chat->user_two_id])) {
            abort(403, 'Unauthorized');
        }
    
        $cacheKey = "chat_messages_{$chat->id}";
    
        // جلب الرسائل من الكاش أو قاعدة البيانات
        $messages = Cache::remember($cacheKey, now()->forever(), function () use ($chat) {
            return $chat->messages()
                ->with('sender')
                ->orderByDesc('created_at')
                ->limit(100)
                ->get()
                ->reverse()
                ->values(); // نحطهم من الأقدم للأحدث
        });
    
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

    $chat = Chat::firstOrCreate([
        'user_one_id' => min($sender->id, $receiverId),
        'user_two_id' => max($sender->id, $receiverId),
    ]);

    $message = Message::create([
        'chat_id'     => $chat->id,
        'sender_id'   => $sender->id,
        'receiver_id' => $receiverId,
        'content'     => $request->content,
    ]);

    $message->load('sender');

    //  تحديث الكاش
    $cacheKey = "chat_messages_{$chat->id}";
    $cachedMessages = Cache::get($cacheKey);

    if ($cachedMessages) {
        $cachedMessages->push($message);
        $cachedMessages = $cachedMessages->take(-100); // نحتفظ بآخر 100 فقط
        Cache::put($cacheKey, $cachedMessages, now()->addMinutes(10));
    }

    broadcast(new MessageSent($message))->toOthers();

    return response()->json(['message' => $message], 201);
}}
