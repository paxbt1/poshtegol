<?php

namespace App\Services;

use App\Models\SettlementPeriod;
use App\Models\UserPeriodResult;

class ExportService
{
    public function settlementCsv(SettlementPeriod $period): string
    {
        $rows = UserPeriodResult::with('user')->where('period_id', $period->id)->orderBy('rank')->get();
        $csv = "name,mobile,card,reward_amount,referral_bonus_amount,final_settlement_amount,settlement_status\n";

        foreach ($rows as $row) {
            $card = $row->user->card_last4 ? '**** **** **** '.$row->user->card_last4 : '';
            $csv .= sprintf(
                "\"%s\",\"%s\",\"%s\",%d,%d,%d,\"%s\"\n",
                $row->user->full_name,
                $row->user->mobile,
                $card,
                $row->reward_amount,
                $row->referral_bonus_amount,
                $row->final_settlement_amount,
                $row->settlement_status,
            );
        }

        return $csv;
    }
}
