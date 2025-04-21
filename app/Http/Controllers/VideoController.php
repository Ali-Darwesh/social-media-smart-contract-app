<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoStoreRequest;
use App\Http\Requests\VideoUpdateRequest;
use App\Services\VideoService;
use App\Models\Video;
use App\Models\Post;

class VideoController extends Controller
{
    public function __construct(
        protected VideoService $service
    ) {}

    public function store(VideoStoreRequest $request)
    {
        $video = $this->service->uploadVideo(
            $request->file('video'),
            Post::find($request->post_id),
            $request->only(['title', 'description'])
        );

        return response()->json([
            'success' => true,
            'data' => $video
        ], 201);
    }

    public function destroy(Video $video)
    {
        $this->service->deleteVideo($video);
        
        return response()->json([
            'success' => true,
            'message' => 'Video deleted'
        ]);
    }

    public function getPostVideos(Post $post)
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getPostVideos($post)
        ]);
    }

    public function update(VideoUpdateRequest $request, Video $video)
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->updateVideoMetadata($video, $request->validated())
        ]);
    }
}