<?php

namespace App\Services;

use App\Models\SettlementPeriod;
use App\Models\UserPeriodResult;

class ExportService
{
    public function settlementCsv(SettlementPeriod $period): string
    {
        $rows = UserPeriodResult::with('user')->where('period_id', $period->id)->orderBy('rank')->get();
        $csv = "name,mobile,total_entry_tokens,reward_tokens,referral_bonus_tokens,settlement_side,settlement_tokens,settlement_status\n";

        foreach ($rows as $row) {
            $side = str_contains((string) $row->settlement_status, 'debtor') ? 'debtor' : (str_contains((string) $row->settlement_status, 'creditor') ? 'creditor' : 'balanced');
            $csv .= sprintf(
                "\"%s\",\"%s\",%d,%d,%d,\"%s\",%d,\"%s\"\n",
                $row->user->full_name,
                $row->user->mobile,
                $row->total_entry_amount,
                $row->reward_amount,
                $row->referral_bonus_amount,
                $side,
                $row->final_settlement_amount,
                $row->settlement_status,
            );
        }

        return $csv;
    }
}
