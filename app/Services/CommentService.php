<?php

namespace App\Services;

use App\Models\Comment;

class CommentService
{
    /**
     * إنشاء تعليق جديد
     */
    public function createComment(array $data): Comment
    {
        return Comment::create([
            'content' => $data['content'],
            'post_id' => $data['post_id'],
            'author_id' => $data['author_id']
        ]);
    }

    /**
     * تحديث تعليق موجود
     */
    public function updateComment(Comment $comment, array $data): Comment
    {
        $comment->update([
            'content' => $data['content'] ?? $comment->content
        ]);

        return $comment;
    }

    /**
     * حذف تعليق
     */
    public function deleteComment(Comment $comment): bool
    {
        return $comment->delete();
    }

    /**
     * الحصول على تعليق بواسطة ID
     */
    public function getCommentById(int $id): ?Comment
    {
        return Comment::find($id);
    }

    /**
     * الحصول على تعليقات منشور معين
     */
    public function getCommentsForPost(int $postId)
    {
        return Comment::where('post_id', $postId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}