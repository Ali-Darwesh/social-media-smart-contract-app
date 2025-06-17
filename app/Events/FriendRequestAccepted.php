<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class FriendRequestAccepted implements ShouldBroadcast
{
    use SerializesModels;

    public $accepter; // المستخدم يلي قبل الطلب
    public $receiver_id; // يلي أرسل الطلب الأصلي

    public function __construct(User $accepter, $receiver_id)
    {
        $this->accepter = $accepter;
        $this->receiver_id = $receiver_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->receiver_id); // قناة خاصة للمستخدم يلي بعت الطلب
    }

    public function broadcastWith()
    {
        return [
            'message' => "{$this->accepter->name} وافق على طلب الصداقة",
            'user_id' => $this->accepter->id,
            'user_name' => $this->accepter->name,
        ];
    }
}
