<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewPostFromFriend extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;
    protected $friend;

    public function __construct($post, $friend)
    {
        $this->post = $post;
        $this->friend = $friend;
    }

    /**
     * Channels: database + broadcast
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Save to database
     */
    public function toDatabase($notifiable)
    {
        return [
            'friend_id' => $this->friend->id,
            'friend_name' => $this->friend->name,
            'post_id' => $this->post->id,
            'post_content' => $this->post->content,
        ];
    }

    /**
     * Broadcast to Echo/Pusher
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'friend_id' => $this->friend->id,
            'friend_name' => $this->friend->name,
            'post_id' => $this->post->id,
            'post_content' => $this->post->content,
        ]);
    }
}
