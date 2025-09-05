<?php

namespace App\Http\Requests\Contract;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // add auth logic if needed
    }

    public function rules(): array
    {
        return [
            'address' => 'required|string|max:255',
            'user_id' => 'required|int|exists:users,id',
            // 'contract_address' => 'required|string|unique:contracts,contract_address',
            // 'client' => 'required|string|max:255',
            // 'serviceProvider' => 'required|string|different:client_address',
            // 'totalAmount' => 'required|integer|min:1',
            // 'status' => 'required|in:Draft,PendingApproval,Active,Rejected,Completed',
        ];
    }
}
