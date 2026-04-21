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

class ProfitLossExport implements FromArray, WithHeadings, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $startDate = $this->request->get('start_date') ? Carbon::parse($this->request->get('start_date')) : Carbon::now()->startOfYear();
        $endDate = $this->request->get('end_date') ? Carbon::parse($this->request->get('end_date')) : Carbon::now();

        $data = [];
        $data[] = ['REVENUES'];

        $revenues = ChartOfAccount::whereIn('account_type', ['Revenue', 'REVENUE'])->active()->get();
        $totalRevenue = 0;

        foreach ($revenues as $revenue) {
            $debits = GeneralLedger::where('account_id', $revenue->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'DEBIT')
                ->sum('amount');
            $credits = GeneralLedger::where('account_id', $revenue->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'CREDIT')
                ->sum('amount');

            $amount = $credits - $debits;

            if ($amount != 0) {
                $data[] = [$revenue->account_code, $revenue->account_name, $amount];
                $totalRevenue += $amount;
            }
        }

        $data[] = ['', 'Total Revenues', $totalRevenue];
        $data[] = [];

        $data[] = ['EXPENSES'];

        $expenses = ChartOfAccount::whereIn('account_type', ['Expense', 'EXPENSE'])->active()->get();
        $totalExpenses = 0;

        foreach ($expenses as $expense) {
            $debits = GeneralLedger::where('account_id', $expense->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'DEBIT')
                ->sum('amount');
            $credits = GeneralLedger::where('account_id', $expense->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'CREDIT')
                ->sum('amount');

            $amount = $debits - $credits;

            if ($amount != 0) {
                $data[] = [$expense->account_code, $expense->account_name, $amount];
                $totalExpenses += $amount;
            }
        }

        $data[] = ['', 'Total Expenses', $totalExpenses];
        $data[] = [];

        $netProfit = $totalRevenue - $totalExpenses;
        $data[] = ['', 'NET PROFIT/(LOSS)', $netProfit];

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
}
