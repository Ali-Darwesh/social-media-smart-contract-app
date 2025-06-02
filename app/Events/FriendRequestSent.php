<?php

namespace App\Events;

use App\Models\User;
use App\Notifications\FreindshipNotification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent implements ShouldBroadcast
{
    use SerializesModels;


    public $sender;
    public $toUser;
    public function __construct(User $sender, User $toUser)
    {
        $this->sender = $sender;
        $this->toUser = $toUser;
    }

    public function broadcastOn()
    {
        new PrivateChannel('friend.requests.' . $this->sender->id);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->sender->id,
            'name' => $this->sender->name,
        ];
    }
}
