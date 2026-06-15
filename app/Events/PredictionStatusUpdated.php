<?php

namespace App\Events;

use App\Models\PredictionEntry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PredictionStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public PredictionEntry $prediction) {}
}
