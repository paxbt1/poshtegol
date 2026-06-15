<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialLedger;
use App\Models\PaymentTransaction;
use App\Models\PeriodSettlement;

class FinanceReportController extends Controller
{
    public function __invoke()
    {
        return view('admin.finance-report', [
            'gatewayFees' => PaymentTransaction::where('status', 'paid')->sum('gateway_fee_amount'),
            'totalPaid' => PaymentTransaction::where('status', 'paid')->sum('amount'),
            'settlements' => PeriodSettlement::with('period')->latest()->get(),
            'ledgers' => FinancialLedger::latest()->take(50)->get(),
        ]);
    }
}
