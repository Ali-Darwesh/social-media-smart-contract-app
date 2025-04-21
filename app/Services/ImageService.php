<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public function uploadImage($file, $imageableType, $imageableId): Image
    {
        return DB::transaction(function () use ($file, $imageableType, $imageableId) {
            $path = $file->store('images', 'public');
            
            $model = $imageableType::findOrFail($imageableId);
            
            return $model->images()->create([
                'url' => Storage::url($path),
                'type' => $file->getClientMimeType()
            ]);
        });
    }

    public function deleteImage(Image $image): void
    {
        DB::transaction(function () use ($image) {
            $path = str_replace('/storage/', '', $image->url);
            Storage::disk('public')->delete($path);
            $image->delete();
        });
    }

    public function getModelImages(string $type, int $id)
    {
        $model = $type::with('images')->findOrFail($id);
        return $model->images;
    }
}