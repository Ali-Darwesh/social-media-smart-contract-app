<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CommentOnPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        // هيك رح يشتغل عبر قاعدة البيانات + عبر WebSocket
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return $this->notificationPayload();
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->notificationPayload());
    }

    protected function notificationPayload()
    {
        return [
            'comment_id' => $this->comment->id,
            'comment_content' => $this->comment->content,
            'post_id' => $this->comment->post_id,
            'post_author_id' => $this->comment->post->author_id,
            'commenter_id' => $this->comment->author->id,
            'commenter_name' => $this->comment->author->name,
            'message' => "{$this->comment->author->name} قام بالتعليق على منشورك"
        ];
    }
}
