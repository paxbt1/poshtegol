<?php

namespace App\Events;

use App\Models\PeriodSettlement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PeriodSettlementCalculated
{
    use Dispatchable, SerializesModels;

    public function __construct(public PeriodSettlement $settlement) {}
}
