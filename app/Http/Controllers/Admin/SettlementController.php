<?php

namespace App\Http\Controllers\Admin;

use App\Events\PeriodSettlementCalculated;
use App\Events\PeriodSettlementFinalized;
use App\Http\Controllers\Controller;
use App\Models\ReferralCommission;
use App\Models\SettlementPeriod;
use App\Models\UserPeriodResult;
use App\Services\ExportService;
use App\Services\SettlementService;

class SettlementController extends Controller
{
    public function show(SettlementPeriod $period)
    {
        return view('admin.settlement-show', [
            'period' => $period,
            'settlement' => $period->settlement,
            'rows' => UserPeriodResult::with('user')->where('period_id', $period->id)->orderBy('rank')->get(),
            'commissions' => ReferralCommission::with(['inviter', 'referred'])->where('period_id', $period->id)->get(),
        ]);
    }

    public function calculate(SettlementPeriod $period, SettlementService $service)
    {
        $settlement = $service->calculatePeriod($period, true);
        event(new PeriodSettlementCalculated($settlement));

        return response()->json(['message' => 'محاسبه آزمایشی تسویه انجام شد.']);
    }

    public function finalize(SettlementPeriod $period, SettlementService $service)
    {
        $settlement = $service->finalizePeriod($period);
        event(new PeriodSettlementFinalized($settlement));

        return response()->json(['message' => 'تسویه دوره نهایی شد.']);
    }

    public function markPaid(SettlementPeriod $period, SettlementService $service)
    {
        $service->markPaid($period);

        return response()->json(['message' => 'پرداخت تسویه ثبت شد.']);
    }

    public function export(SettlementPeriod $period, ExportService $exportService)
    {
        return response($exportService->settlementCsv($period), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="settlement-'.$period->id.'.csv"',
        ]);
    }
}
