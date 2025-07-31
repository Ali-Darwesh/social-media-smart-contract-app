<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class likePostNotification extends Notification
{
    use Queueable;

    public $post;
    public $likedBy;

    public function __construct(Post $post, User $likedBy)
    {
        $this->post = $post;
        $this->likedBy = $likedBy;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'post_id' => $this->post->id,
            'post_content' => $this->post->content,
            'liked_by_id' => $this->likedBy->id,
            'liked_by_name' => $this->likedBy->name,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'post_id' => $this->post->id,
            'post_content' => $this->post->content,
            'liked_by_id' => $this->likedBy->id,
            'liked_by_name' => $this->likedBy->name,
        ]);
    }
}
