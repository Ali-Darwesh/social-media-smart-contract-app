<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent implements ShouldBroadcast
{
    use SerializesModels;

    public $sender;

    public function __construct(User $sender)
    {
        $this->sender = $sender;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('friend.requests.' . $this->sender->id);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->sender->id,
            'name' => $this->sender->name,
        ];
    }
}
