<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Models\Post;
use App\Models\PostReaction;
use App\Services\PostService;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
        $this->middleware('auth:api,admin-api');
    }

    public function index()
    {
        $posts = Post::with(['author', 'images', 'videos', 'comments'])
            ->withCount([
                'likes as likes_count',
                'dislikes as dislikes_count',
            ])
            ->with(['reactions' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->latest()
            ->paginate(10);
    
        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }
    

    public function store(PostStoreRequest $request)
    {
        $post = $this->postService->createPost(
            $request->only(['content', 'details']) + ['author_id' => auth()->id()],
            $request->file('images', []),
            $request->file('videos', [])
        );

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post
        ], 201);
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $post->load(['author', 'images', 'videos', 'comments'])
        ]);
    }

    public function update(PostUpdateRequest $request, Post $post)
    {
        if ($post->author_id !== auth()->id()) {
            $this->authorize('update any post');
        } else {
            $this->authorize('update own post');
        }
        $post = $this->postService->updatePost(
            $post,
            $request->only(['content', 'details']),
            $request->file('new_images', []),
            $request->input('deleted_images', []),
            $request->file('new_videos', []),
            $request->input('deleted_videos', [])
        );

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post
        ]);
    }

    public function destroy(Post $post)
    {
        if ($post->author_id !== auth()->id()) {
            $this->authorize('delete any post');
        } else {
            $this->authorize('delete own post');
        }
        $this->postService->deletePost($post);

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }

    public function addLike(Post $post)
{
    PostReaction::updateOrCreate(
        ['post_id' => $post->id, 'user_id' => auth()->id()],
        ['type' => 'like']
    );

    return response()->json(['message' => 'Liked']);
}

public function addDislike(Post $post)
{
    PostReaction::updateOrCreate(
        ['post_id' => $post->id, 'user_id' => auth()->id()],
        ['type' => 'dislike']
    );

    return response()->json(['message' => 'Disliked']);
}
public function removeReaction(Post $post)
{
    $deleted = PostReaction::where('post_id', $post->id)
        ->where('user_id', auth()->id())
        ->delete();

    return response()->json([
        'message' => $deleted ? 'Reaction removed' : 'No reaction to remove',
    ]);
}

/*
    public function adminIndex()
    {
        if (!auth()->user()->hasRole('admin')) {
            throw new UnauthorizedException(403, 'This action is restricted to admins only');
        }

        $posts = Post::with(['author', 'images', 'videos'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }*/
}