<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class StoreClauseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => 'required|string|max:1000',
            'amount_usd' => 'required|integer|min:1',
            'due_date' => 'required|date|after_or_equal:today',
        ];
    }
}
