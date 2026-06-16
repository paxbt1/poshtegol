<?php

namespace App\Http\Requests\Auth;

use App\Services\PersianInputNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'regex:/^09\d{9}$/', 'unique:users,mobile'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'نام را وارد کنید.',
            'first_name.max' => 'نام نباید بیشتر از ۱۰۰ نویسه باشد.',
            'last_name.required' => 'نام خانوادگی را وارد کنید.',
            'last_name.max' => 'نام خانوادگی نباید بیشتر از ۱۰۰ نویسه باشد.',
            'mobile.required' => 'شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل باید با فرمت 09xxxxxxxxx باشد.',
            'mobile.unique' => 'این شماره موبایل قبلا ثبت شده است.',
            'password.required' => 'رمز عبور را وارد کنید.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
            'password.min' => 'رمز عبور باید حداقل ۸ نویسه باشد.',
        ];
    }
}
