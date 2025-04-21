<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->comment->author_id === auth()->id();
    }

    public function rules()
    {
        return [
            'content' => 'sometimes|string|max:1000'
        ];
    }
}