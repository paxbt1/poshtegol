<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FinanceSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gateway_fee_percent' => ['required', 'numeric', 'min:0', 'max:30'],
            'referral_rate' => ['required', 'numeric', 'min:0', 'max:20'],
            'referral_enabled_until_group_stage' => ['nullable', 'boolean'],
            'group_entry_amount' => ['required', 'integer', 'min:0'],
            'round32_entry_amount' => ['required', 'integer', 'min:0'],
            'round16_entry_amount' => ['required', 'integer', 'min:0'],
            'quarter_final_entry_amount' => ['required', 'integer', 'min:0'],
            'semi_final_entry_amount' => ['required', 'integer', 'min:0'],
            'bronze_final_entry_amount' => ['required', 'integer', 'min:0'],
            'final_entry_amount' => ['required', 'integer', 'min:0'],
        ];
    }
}
