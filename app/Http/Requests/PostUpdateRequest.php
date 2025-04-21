<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $post = $this->route('post');
        $user = auth()->user();
        
        return ($user->can('update own post') && $post->author_id == $user->id )|| 
                $user->can('manage all');
    }

    public function rules()
    {
        return [
            'content' => 'sometimes|string|max:5000',
            'details' => 'nullable|string|max:1000',
            'new_images' => 'nullable|array|max:10',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'exists:images,id',
            'new_videos' => 'nullable|array|max:3',
            'new_videos.*' => 'mimetypes:video/mp4,video/quicktime|max:102400',
            'deleted_videos' => 'nullable|array',
            'deleted_videos.*' => 'exists:videos,id'
        ];
    }
}