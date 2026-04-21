<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportSummaryExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        return [
            new SalesReportSummarySheet($this->metrics),
            new SalesReportExport($this->sales, $this->period, $this->metrics),
        ];
    }
}

class SalesReportSummarySheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $metrics;

    public function __construct($metrics)
    {
        $this->metrics = $metrics;
    }

    public function array(): array
    {
        return [
            ['Metric', 'Value'],
            ['Total Sales', $this->metrics['totalSales'] ?? 0],
            ['Total Items', $this->metrics['totalItems'] ?? 0],
            ['Total Tax', $this->metrics['totalTax'] ?? 0],
            ['Total Discount', $this->metrics['totalDiscount'] ?? 0],
            ['Total Making Charges', $this->metrics['totalMakingCharges'] ?? 0],
            ['Total Paid', $this->metrics['totalPaid'] ?? 0],
            ['Total Outstanding', $this->metrics['totalOutstanding'] ?? 0],
            ['Cost of Goods Sold', $this->metrics['costOfGoodsSold'] ?? 0],
            ['Gross Profit', $this->metrics['grossProfit'] ?? 0],
            ['Gross Margin %', $this->metrics['grossMargin'] ?? 0],
            ['Average Order Value', $this->metrics['averageOrderValue'] ?? 0],
            ['Total Transactions', $this->metrics['count'] ?? 0],
        ];
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a1a1a']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
