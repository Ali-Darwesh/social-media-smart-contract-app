<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Notifications\CommentOnPostNotification; 
class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
        
        // سياسات المصادقة
        $this->middleware('auth:api');
       // $this->middleware('can:view,comment')->only('show');
       // $this->middleware('can:update,comment')->only('update');
       // $this->middleware('can:delete,comment')->only('destroy');
    }

    /**
     * عرض جميع التعليقات
     */
    public function index(): JsonResponse
    {
        $comments = Comment::with(['author', 'post'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $this->formatComments($comments),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage()
            ]
        ]);
    }

    /**
     * إنشاء تعليق جديد
     */
    public function store(CommentStoreRequest $request): JsonResponse
    {
        $comment = $this->commentService->createComment(
            $request->validated() + ['author_id' => auth()->id()]
        );
        // send Comment On Post Notification
    $postAuthor = $comment->post->author;

    if ($postAuthor->id !== auth()->id()) {
        $postAuthor->notify(new CommentOnPostNotification($comment));
    }

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء التعليق بنجاح',
            'data' => $this->formatSingleComment($comment)
        ], Response::HTTP_CREATED);
    }

    /**
     * عرض تعليق محدد
     */
    public function show(Comment $comment): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatSingleComment($comment->load(['author', 'post']))
        ]);
    }

    /**
     * تحديث التعليق
     */
    public function update(CommentUpdateRequest $request, Comment $comment): JsonResponse
    {
       // $this->authorize('update', $comment);

        $comment = $this->commentService->updateComment(
            $comment,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التعليق بنجاح',
            'data' => $this->formatSingleComment($comment)
        ]);
    }

    /**
     * حذف التعليق
     */
    public function destroy(Comment $comment): JsonResponse
    {
       // $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التعليق بنجاح'
        ]);
    }

    /**
     * تنسيق بيانات التعليقات
     */
    protected function formatComments($comments): array
    {
        return $comments->map(function ($comment) {
            return $this->formatCommentData($comment);
        })->toArray();
    }

    /**
     * تنسيق بيانات تعليق واحد
     */
    protected function formatSingleComment(Comment $comment): array
    {
        return $this->formatCommentData($comment);
    }

    /**
     * هيكلة بيانات التعليق
     */
    protected function formatCommentData(Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at->diffForHumans(),
            'author' => [
                'id' => $comment->author->id,
                'name' => $comment->author->name,
                'email' => $comment->author->email
            ],
            'post' => [
                'id' => $comment->post->id,
                'title' => $comment->post->title
            ]
        ];
    }
}