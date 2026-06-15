<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\CardNumberService;
use App\Services\PersianInputNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => PersianInputNormalizer::mobile($this->input('mobile')),
            'card_number' => PersianInputNormalizer::card($this->input('card_number')),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'regex:/^09\d{9}$/', 'unique:users,mobile'],
            'card_number' => ['required', 'digits:16'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $service = app(CardNumberService::class);
            $card = (string) $this->input('card_number');

            if ($card !== '' && ! $service->passesLuhn($card)) {
                $validator->errors()->add('card_number', 'شماره کارت معتبر نیست.');
                return;
            }

            if ($card !== '' && User::where('card_hash', $service->hash($card))->exists()) {
                $validator->errors()->add('card_number', 'این شماره کارت قبلا برای عضو دیگری ثبت شده است.');
            }
        });
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
            'card_number.required' => 'شماره کارت را وارد کنید.',
            'card_number.digits' => 'شماره کارت باید ۱۶ رقم باشد.',
            'password.required' => 'رمز عبور را وارد کنید.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
            'password.min' => 'رمز عبور باید حداقل ۸ نویسه باشد.',
        ];
    }
}
