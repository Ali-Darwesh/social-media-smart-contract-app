<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => 'sometimes|string|max:1000',
            'approved_by_a' => 'sometimes|boolean',
            'approved_by_b' => 'sometimes|boolean',
            'amount_usd' => 'sometimes|integer|min:1',
        ];
    }
}
