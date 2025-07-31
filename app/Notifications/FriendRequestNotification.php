<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FriendRequestNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $sender;

    public function __construct($sender)
    {
        $this->sender = $sender;
    }

    public function via(object $notifiable)
    {
        // Save the notifiable for use in broadcastOn
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable)
    {
        return [
            'message' => "{$this->sender->name} send friend request",
            'from_user_id' => $this->sender->id,
        ];
    }
    /**
     * Get the type of the notification being broadcast.
     */



    public function toBroadcast(object $notifiable)
    {
        return new BroadcastMessage([
            'message' => "{$this->sender->name} send friend request",
            'from_user_id' => $this->sender->id,
        ]);
    }
}
