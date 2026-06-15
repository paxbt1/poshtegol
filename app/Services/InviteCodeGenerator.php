<?php

namespace App\Services;

use App\Models\User;
use App\Models\InviteLink;
use Illuminate\Support\Str;

class InviteCodeGenerator
{
    public function make(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('invite_code', $code)->exists() || InviteLink::where('code', $code)->exists());

        return $code;
    }
}
