<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ImageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                File::image()
                    ->max(2048) // 2MB
                    ->mimes(['jpeg', 'png', 'jpg', 'gif'])
            ],
            'imageable_type' => 'required|string|in:App\Models\Post,App\Models\User',
            'imageable_id' => 'required|integer|exists:'. $this->imageable_type .',id'
        ];
    }
}