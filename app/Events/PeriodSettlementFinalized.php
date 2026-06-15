<?php

namespace App\Events;

use App\Models\PeriodSettlement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PeriodSettlementFinalized
{
    use Dispatchable, SerializesModels;

    public function __construct(public PeriodSettlement $settlement) {}
}
