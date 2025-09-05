<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Image;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PostService
{
    public function createPost(array $data, $images = [], $videos = [])
    {
        $post = Post::create($data);

        $this->handleImages($post, $images);
        $this->handleVideos($post, $videos);

        return $post->load('images', 'videos');
    }

    public function updatePost(Post $post, array $data, $newImages = [], $deletedImages = [], $newVideos = [], $deletedVideos = [])
    {
        $user = auth()->user();
        
        // التحقق من الصلاحيات للتعديل
        if (!$user->can('update own post') || ($post->author_id != $user->id && !$user->can('manage all'))) {
            throw new UnauthorizedException(403, 'You are not authorized to update this post');
        }

        

        $this->deleteImages($post, $deletedImages);
        $this->handleImages($post, $newImages);
        
        $this->deleteVideos($post, $deletedVideos);
        $this->handleVideos($post, $newVideos);
        $post->update($data);
        return $post->fresh(['images', 'videos']);
    }

    protected function handleImages(Post $post, array $images)
    {
        foreach ($images as $image) {
            $path = $image->store('posts/images', 'public');
            $post->images()->create([
                'url' => $path,
                'type' => 'post_image'
            ]);
        }
    }

    protected function handleVideos(Post $post, array $videos)
    {
        foreach ($videos as $video) {
            $path = $video->store('posts/videos', 'public');
            $post->videos()->create([
                'url' =>$path,
                'type' => 'post_video'
            ]);
        }
    }

    protected function deleteImages(Post $post, array $imageIds)
    {
        $images = $post->images()->whereIn('id', $imageIds)->get();
        foreach ($images as $image) {
            Storage::disk('public')->delete(str_replace(asset('storage/'), '', $image->url));
            $image->delete();
        }
    }

    protected function deleteVideos(Post $post, array $videoIds)
    {
        $videos = $post->videos()->whereIn('id', $videoIds)->get();
        foreach ($videos as $video) {
            Storage::disk('public')->delete(str_replace(asset('storage/'), '', $video->url));
            $video->delete();
        }
    }

    public function deletePost(Post $post)
    {
        $user = auth()->user();
        
        // التحقق من الصلاحيات للحذف
        if (!$user->can('delete own post') || ($post->author_id != $user->id && !$user->can('delete any post'))) {
            throw new UnauthorizedException(403, 'You are not authorized to delete this post');
        }

        $this->deleteImages($post, $post->images->pluck('id')->toArray());
        $this->deleteVideos($post, $post->videos->pluck('id')->toArray());
        
        $post->comments()->delete();
        $post->delete();
    }
}