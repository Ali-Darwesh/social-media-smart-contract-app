<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostStoreRequest extends FormRequest
{
    public function authorize()
    {
       // return auth()->user()->can('create post');
       return true;
    }

    public function rules()
    {
        return [
            'content' => 'required|string|max:5000',
            'details' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'file|image|mimes:jpeg,png,jpg,gif|max:2024000',
            'videos' => 'nullable|array|max:3',
            'videos.*' => 'file|mimetypes:video/*,video/quicktime|max:2024000'
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'محتوى المنشور مطلوب',
            'images.max' => 'يمكنك رفع 10 صور',
            'videos.max' => 'يمكنك رفع 3 فيديوهات',
            'videos.*.max' => 'حجم الفيديو يجب ألا يتجاوز 100MB'
        ];
    }
}