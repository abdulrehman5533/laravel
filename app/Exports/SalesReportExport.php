<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $sales;

    protected $period;

    protected $metrics;

    public function __construct($sales, $period, $metrics)
    {
        $this->sales = $sales;
        $this->period = $period;
        $this->metrics = $metrics;
    }

    public function array(): array
    {
        $data = [];

        if ($this->sales->isEmpty()) {
            return [];
        }

        foreach ($this->sales as $sale) {
            $data[] = [
                $sale->id,
                Carbon::parse($sale->sale_time)->format('Y-m-d H:i'),
                $sale->customer?->name ?? 'Walk-in',
                $sale->customer?->phone ?? 'N/A',
                $sale->items->count(),
                number_format($sale->total, 2),
                number_format($sale->tax_amount ?? 0, 2),
                number_format($sale->discount ?? 0, 2),
                number_format($sale->making_charges ?? 0, 2),
                number_format($sale->payments()->sum('amount'), 2),
                number_format($sale->outstanding_balance ?? 0, 2),
                $sale->status,
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Invoice ID',
            'Sale Time',
            'Customer',
            'Phone',
            'Items',
            'Amount',
            'Tax',
            'Discount',
            'Making Charges',
            'Paid',
            'Outstanding',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a1a1a']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Sales Report';
    }
}
