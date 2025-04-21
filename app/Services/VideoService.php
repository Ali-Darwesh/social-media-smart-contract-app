<?php

namespace App\Services;

use App\Models\Video;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VideoService
{
    public function uploadVideo($file, Post $post, array $metadata = []): Video
    {
        return DB::transaction(function () use ($file, $post, $metadata) {
            $path = $file->store('videos', 'public');
            
            return $post->videos()->create([
                'url' => Storage::url($path),
                'title' => $metadata['title'] ?? null,
                'description' => $metadata['description'] ?? null,
                'type' => $file->getClientMimeType()
            ]);
        });
    }

    public function deleteVideo(Video $video): void
    {
        DB::transaction(function () use ($video) {
            $path = str_replace('/storage/', '', $video->url);
            Storage::disk('public')->delete($path);
            $video->delete();
        });
    }

    public function updateVideoMetadata(Video $video, array $data): Video
    {
        $video->update($data);
        return $video->fresh();
    }

    public function getPostVideos(Post $post)
    {
        return $post->videos()->latest()->get();
    }
}