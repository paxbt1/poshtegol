<?php

namespace App\Events;

use App\Models\FootballMatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(public FootballMatch $match) {}
}
