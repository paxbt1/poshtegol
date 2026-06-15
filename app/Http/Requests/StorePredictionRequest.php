<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePredictionRequest extends FormRequest
{
    public function rules(): array
    {
        $match = $this->route('match');
        $teamIds = $match ? array_filter([$match->home_team_id, $match->away_team_id]) : [];
        $isGroup = $match?->stage === 'group';
        $requiresQualifiedTeam = ! $isGroup && ! $match?->is_placeholder_match;

        return [
            'full_time_result' => ['required', Rule::in(['home', 'draw', 'away'])],
            'exact_home_score' => ['required', 'integer', 'min:0', 'max:9'],
            'exact_away_score' => ['required', 'integer', 'min:0', 'max:9'],
            'total_goals_option' => ['required', Rule::in(['under_2_5', 'over_2_5'])],
            'qualified_team_id' => [$requiresQualifiedTeam ? 'required' : 'nullable', 'nullable', Rule::in($teamIds)],
        ];
    }

    public function messages(): array
    {
        return [
            'full_time_result.required' => 'نتیجه نهایی را انتخاب کنید.',
            'full_time_result.in' => 'نتیجه نهایی معتبر نیست.',
            'exact_home_score.required' => 'گل تیم میزبان را انتخاب کنید.',
            'exact_away_score.required' => 'گل تیم مهمان را انتخاب کنید.',
            'exact_home_score.max' => 'نتیجه دقیق باید بین ۰ تا ۹ باشد.',
            'exact_away_score.max' => 'نتیجه دقیق باید بین ۰ تا ۹ باشد.',
            'total_goals_option.required' => 'گزینه مجموع گل‌ها را انتخاب کنید.',
            'qualified_team_id.required' => 'تیم صعودکننده را انتخاب کنید.',
            'qualified_team_id.in' => 'تیم صعودکننده باید یکی از دو تیم همین بازی باشد.',
        ];
    }
}
