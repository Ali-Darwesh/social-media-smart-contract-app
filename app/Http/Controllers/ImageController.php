<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageStoreRequest;
use App\Services\ImageService;
use App\Models\Image;
use App\Models\Post;

class ImageController extends Controller
{
    public function __construct(
        protected ImageService $service
    ) {}

    public function store(ImageStoreRequest $request)
    {
        $image = $this->service->uploadImage(
            $request->file('image'),
            $request->imageable_type,
            $request->imageable_id
        );

        return response()->json([
            'success' => true,
            'data' => $image
        ], 201);
    }

    public function destroy(Image $image)
    {
        $this->service->deleteImage($image);
        
        return response()->json([
            'success' => true,
            'message' => 'Image deleted'
        ]);
    }

    public function getPostImages(Post $post)
    {
        $images = $this->service->getModelImages(Post::class, $post->id);
        
        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    }
}