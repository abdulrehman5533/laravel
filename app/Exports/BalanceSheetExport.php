<?php

namespace App\Exports;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalanceSheetExport implements FromArray, WithHeadings, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $asAtDate = $this->request->get('as_at_date') ? Carbon::parse($this->request->get('as_at_date')) : Carbon::now();

        $data = [];
        $data[] = ['ASSETS'];

        $assets = ChartOfAccount::whereIn('account_type', ['Asset', 'ASSET'])->active()->get();
        $totalAssets = 0;

        foreach ($assets as $asset) {
            $amount = $this->getAccountBalance($asset->id, $asAtDate);
            if ($amount != 0) {
                $data[] = [$asset->account_code, $asset->account_name, $amount];
                $totalAssets += $amount;
            }
        }

        $data[] = ['', 'Total Assets', $totalAssets];
        $data[] = [];

        $data[] = ['LIABILITIES'];

        $liabilities = ChartOfAccount::whereIn('account_type', ['Liability', 'LIABILITY'])->active()->get();
        $totalLiabilities = 0;

        foreach ($liabilities as $liability) {
            $amount = $this->getAccountBalance($liability->id, $asAtDate);
            if ($amount != 0) {
                $data[] = [$liability->account_code, $liability->account_name, $amount];
                $totalLiabilities += $amount;
            }
        }

        $data[] = ['', 'Total Liabilities', $totalLiabilities];
        $data[] = [];

        $data[] = ['EQUITY'];

        $equity = ChartOfAccount::whereIn('account_type', ['Equity', 'EQUITY'])->active()->get();
        $totalEquity = 0;

        foreach ($equity as $eq) {
            $amount = $this->getAccountBalance($eq->id, $asAtDate);
            if ($amount != 0) {
                $data[] = [$eq->account_code, $eq->account_name, $amount];
                $totalEquity += $amount;
            }
        }

        $revenueAccounts = ChartOfAccount::whereIn('account_type', ['Revenue', 'REVENUE'])->active()->get();
        $totalRev = 0;
        foreach ($revenueAccounts as $rev) {
            $totalRev += $this->getAccountBalance($rev->id, $asAtDate);
        }

        $expenseAccounts = ChartOfAccount::whereIn('account_type', ['Expense', 'EXPENSE'])->active()->get();
        $totalExp = 0;
        foreach ($expenseAccounts as $exp) {
            $totalExp += $this->getAccountBalance($exp->id, $asAtDate);
        }

        $currentNetProfit = $totalRev - $totalExp;
        $totalEquity += $currentNetProfit;

        $data[] = ['', 'Total Equity', $totalEquity];
        $data[] = [];

        $data[] = ['', 'Total Liabilities & Equity', $totalLiabilities + $totalEquity];

        return $data;
    }

    public function headings(): array
    {
        return [
            'Account Code',
            'Account Name',
            'Amount',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    private function getAccountBalance($accountId, $asAtDate)
    {
        $account = ChartOfAccount::find($accountId);
        if (! $account) {
            return 0;
        }

        $debits = GeneralLedger::where('account_id', $accountId)
            ->where('created_at', '<=', $asAtDate)
            ->where('type', 'DEBIT')
            ->sum('amount');

        $credits = GeneralLedger::where('account_id', $accountId)
            ->where('created_at', '<=', $asAtDate)
            ->where('type', 'CREDIT')
            ->sum('amount');

        $type = strtoupper($account->account_type);

        if (in_array($type, ['ASSET', 'EXPENSE'])) {
            return $account->opening_balance + $debits - $credits;
        } else {
            return $account->opening_balance + $credits - $debits;
        }
    }
}
