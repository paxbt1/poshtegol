<?php

namespace App\Http\Requests\Auth;

use App\Services\PersianInputNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => PersianInputNormalizer::mobile($this->input('mobile')),
        ]);
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل باید با فرمت 09xxxxxxxxx باشد.',
            'password.required' => 'رمز عبور را وارد کنید.',
        ];
    }
}
