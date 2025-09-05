<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address' => 'sometimes|string|max:255',
            'contract_address' => 'sometimes|string|unique:contracts,contract_address',
            'client' => 'sometimes|string|max:255',
            'serviceProvider' => 'sometimes|string|different:client',
            'totalAmount' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:Draft,PendingApproval,Active,Rejected,Completed',

        ];
    }
}
