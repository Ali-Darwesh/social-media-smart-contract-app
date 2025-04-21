<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterSupervisorRequest extends FormRequest
{
    public function authorize(): bool
    {  echo auth()->guard('admin-api')->check();
echo auth()->guard('admin-api')->check();
        return (auth()->guard('admin-api')->check() && auth()->user()->can('create supervisor account'));
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:supervisors',
            'password' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'حقل الاسم مطلوب',
            'email.required' => 'حقل البريد الإلكتروني مطلوب',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'password.min' => 'كلمة المرور يجب أن تتكون من 8 أحرف على الأقل',
        ];
    }
}