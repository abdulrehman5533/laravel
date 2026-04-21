<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\TaxSlab;
use App\Models\TaxConfiguration;
use App\Models\TaxReport;
use App\Models\PosSale;
use App\Models\PurchaseOrder;
use App\Services\ComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaxComplianceController extends Controller
{
    protected $complianceService;

    public function __construct(ComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    public function index()
    {
        $taxSlabs        = TaxSlab::all();
        $taxConfigs      = TaxConfiguration::with('branch')->latest()->get();
        $amlAlerts       = $this->complianceService->detectAmlSuspiciousActivity();
        $filingHistory   = TaxReport::latest()->take(10)->get();

        // Net Tax Payable summary (current month)
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $outputTax = DB::table('pos_sales')
            ->whereBetween('sale_time', [$start, $end])
            ->where('status', 'completed')
            ->sum('tax_amount');

        $inputTax = DB::table('purchase_orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['received', 'completed'])
            ->sum(DB::raw('COALESCE(gst_amount, 0)'));

        $netTaxPayable = max(0, $outputTax - $inputTax);

        return view('compliance.tax.index', compact(
            'taxSlabs', 'taxConfigs', 'amlAlerts',
            'filingHistory', 'outputTax', 'inputTax', 'netTaxPayable'
        ));
    }

    public function reports(Request $request)
    {
        $period  = $request->get('period', 'monthly');
        $year    = $request->get('year', date('Y'));
        $month   = $request->get('month', date('m'));
        $quarter = $request->get('quarter', ceil(date('m') / 3));

        $report = null;
        if ($period === 'monthly') {
            $report = $this->complianceService->getMonthlyTaxSummary($year, $month);
        } elseif ($period === 'quarterly') {
            $report = $this->complianceService->getQuarterlyTaxSummary($year, $quarter);
        } elseif ($period === 'yearly') {
            $report = $this->complianceService->getYearlyTaxSummary($year);
        }

        return view('compliance.tax.reports', compact('report', 'period', 'year', 'month', 'quarter'));
    }

    public function amlAlerts()
    {
        $alerts = $this->complianceService->detectAmlSuspiciousActivity();
        return view('compliance.aml.alerts', compact('alerts'));
    }

    public function storeSlab(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:191',
            'tax_type'   => 'required|string',
            'rate'       => 'required|numeric|min:0|max:100',
            'hsn_code'   => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        if ($data['is_default'] ?? false) {
            TaxSlab::where('is_default', true)->update(['is_default' => false]);
        }

        TaxSlab::create($data);
        return redirect()->back()->with('success', 'Tax slab added successfully');
    }

    public function storeTaxConfig(Request $request)
    {
        $data = $request->validate([
            'tax_type'         => 'required|string',
            'rate'             => 'required|numeric|min:0|max:100',
            'applicable_to'    => 'required|string',
            'product_category' => 'nullable|string|max:100',
            'hsn_sac_code'     => 'nullable|string|max:20',
            'effective_from'   => 'required|date',
            'effective_to'     => 'nullable|date|after:effective_from',
        ]);

        TaxConfiguration::create($data);
        return redirect()->back()->with('success', 'Tax configuration saved.');
    }

    public function inputTaxCredit(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year  = $request->get('year', date('Y'));
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $purchases = DB::table('purchase_orders')
            ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->whereBetween('purchase_orders.created_at', [$start, $end])
            ->whereIn('purchase_orders.status', ['received', 'completed'])
            ->select(
                'purchase_orders.id',
                'purchase_orders.po_number as order_number',
                'purchase_orders.created_at',
                'purchase_orders.total_amount',
                DB::raw('COALESCE(purchase_orders.gst_amount, 0) as tax_amount'),
                'suppliers.name as supplier_name'
            )
            ->get();

        $totalITC = $purchases->sum('tax_amount');

        return view('compliance.tax.itc', compact('purchases', 'totalITC', 'month', 'year'));
    }

    public function storeFilingRecord(Request $request)
    {
        $data = $request->validate([
            'tax_type'                => 'required|string',
            'period_start'            => 'required|date',
            'period_end'              => 'required|date|after:period_start',
            'total_taxable_sales'     => 'required|numeric|min:0',
            'total_tax_on_sales'      => 'required|numeric|min:0',
            'total_taxable_purchases' => 'required|numeric|min:0',
            'total_tax_on_purchases'  => 'required|numeric|min:0',
            'net_tax_payable'         => 'required|numeric',
            'status'                  => 'required|in:draft,filed,paid',
        ]);

        TaxReport::create($data);
        return redirect()->back()->with('success', 'Filing record saved.');
    }

    public function generateFiling(Request $request)
    {
        $filing = $this->complianceService->generateStatutoryFiling($request->country, $request->period);
        return response()->json($filing);
    }
}
