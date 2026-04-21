<?php

namespace App\Exports;

use App\Models\ChartOfAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrialBalanceExport implements FromArray, WithHeadings, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $asAtDate = $this->request->get('as_at_date') ? Carbon::parse($this->request->get('as_at_date')) : Carbon::now();

        $accounts = ChartOfAccount::active()->with(['ledgerEntries' => function ($query) use ($asAtDate) {
            $query->where('created_at', '<=', $asAtDate);
        }])->orderBy('account_code')->get();

        $totalDebits = 0;
        $totalCredits = 0;
        $data = [];

        foreach ($accounts as $account) {
            $debitSum = $account->ledgerEntries->where('type', 'DEBIT')->sum('amount');
            $creditSum = $account->ledgerEntries->where('type', 'CREDIT')->sum('amount');

            $type = strtoupper($account->account_type);

            if (in_array($type, ['ASSET', 'EXPENSE'])) {
                $balance = $account->opening_balance + $debitSum - $creditSum;
                $debit = $balance > 0 ? $balance : 0;
                $credit = $balance < 0 ? abs($balance) : 0;
            } else {
                $balance = $account->opening_balance + $creditSum - $debitSum;
                $credit = $balance > 0 ? $balance : 0;
                $debit = $balance < 0 ? abs($balance) : 0;
            }

            if ($debit != 0 || $credit != 0) {
                $data[] = [
                    $account->account_code,
                    $account->account_name,
                    $account->account_type,
                    $debit,
                    $credit,
                ];

                $totalDebits += $debit;
                $totalCredits += $credit;
            }
        }

        $data[] = [
            '',
            'TOTAL',
            '',
            $totalDebits,
            $totalCredits,
        ];

        return $data;
    }

    public function headings(): array
    {
        return [
            'Account Code',
            'Account Name',
            'Account Type',
            'Debit',
            'Credit',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
