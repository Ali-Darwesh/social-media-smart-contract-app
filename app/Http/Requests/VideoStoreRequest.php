<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class VideoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'video' => [
                'required',
                File::types(['mp4', 'quicktime'])
                    ->max(102400) // 100MB
            ],
            'post_id' => 'required|exists:posts,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ];
    }
}