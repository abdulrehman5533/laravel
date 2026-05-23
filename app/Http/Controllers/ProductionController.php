<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\GoldRate;
use App\Models\InventoryProduct;
use App\Models\KarigarSettlement;
use App\Models\ProductBom;
use App\Models\ProductBomItem;
use App\Models\ProductionJob;
use App\Models\PurityLevel;
use App\Models\RefineryBatch;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    // ==================== BOM ====================

    public function bomIndex()
    {
        $boms = ProductBom::with(['product', 'purity'])->latest()->paginate(20);
        return view('production.bom.index', compact('boms'));
    }

    public function bomCreate()
    {
        $products = InventoryProduct::active()->orderBy('name')->get();
        $purities = PurityLevel::active()->get();
        return view('production.bom.create', compact('products', 'purities'));
    }

    public function bomStore(Request $request)
    {
        $data = $request->validate([
            'name'                      => 'required|string|max:255',
            'description'               => 'nullable|string',
            'inventory_product_id'      => 'nullable|exists:inventory_products,id',
            'expected_gross_weight'     => 'required|numeric|min:0',
            'expected_net_weight'       => 'required|numeric|min:0',
            'allowed_wastage_percentage'=> 'required|numeric|min:0|max:100',
            'purity_id'                 => 'nullable|exists:purity_levels,id',
            'estimated_labor_cost'      => 'nullable|numeric|min:0',
            'labor_type'                => 'nullable|in:per_gram,per_piece,fixed',
            'items'                     => 'nullable|array',
            'items.*.type'              => 'required|in:metal,stone,other',
            'items.*.item_name'         => 'required|string',
            'items.*.quantity'          => 'required|numeric|min:0',
            'items.*.unit'              => 'required|string',
            'items.*.weight'            => 'nullable|numeric|min:0',
            'items.*.estimated_cost'    => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $data['bom_number'] = 'BOM-' . date('Ymd') . '-' . str_pad(ProductBom::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            $data['is_active']  = true;
            $data['created_by'] = Auth::id();
            $items = $data['items'] ?? [];
            unset($data['items']);

            $bom = ProductBom::create($data);
            foreach ($items as $item) {
                $bom->items()->create($item);
            }
        });

        return redirect()->route('production.bom.index')->with('success', 'BOM created successfully.');
    }

    public function bomShow(ProductBom $bom)
    {
        $bom->load(['product', 'purity', 'items']);
        $jobs = ProductionJob::where('product_bom_id', $bom->id)->with('karigar')->latest()->get();
        return view('production.bom.show', compact('bom', 'jobs'));
    }

    // ==================== PRODUCTION JOBS ====================

    public function jobIndex()
    {
        $jobs = ProductionJob::with(['bom', 'karigar', 'branch'])->latest()->paginate(20);
        $stats = [
            'pending'   => ProductionJob::where('status', 'pending')->count(),
            'issued'    => ProductionJob::where('status', 'issued')->count(),
            'completed' => ProductionJob::where('status', 'completed')->count(),
            'total_issued_weight' => ProductionJob::where('status', 'issued')->sum('metal_weight_issued'),
        ];
        return view('production.jobs.index', compact('jobs', 'stats'));
    }

    public function jobCreate()
    {
        $boms     = ProductBom::where('is_active', true)->get();
        $karigars = Supplier::where('supplier_type', 'Karigar')->where('status', 'active')->get();
        $branches = Branch::where('is_active', true)->get();
        $purities = PurityLevel::active()->get();
        return view('production.jobs.create', compact('boms', 'karigars', 'branches', 'purities'));
    }

    public function jobStore(Request $request)
    {
        $data = $request->validate([
            'product_bom_id'        => 'nullable|exists:product_boms,id',
            'karigar_id'            => 'nullable|exists:suppliers,id',
            'karigar_type'          => 'required|in:internal,external',
            'branch_id'             => 'required|exists:branches,id',
            'expected_delivery_date'=> 'required|date',
            'labor_rate_per_gram'   => 'nullable|numeric|min:0',
            'labor_rate_per_piece'  => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string',
        ]);

        $data['job_number'] = 'JOB-' . date('Ymd') . '-' . str_pad(ProductionJob::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        $data['status']     = 'pending';
        $data['created_by'] = Auth::id();

        $job = ProductionJob::create($data);
        return redirect()->route('production.jobs.show', $job)->with('success', 'Production job created.');
    }

    public function jobShow(ProductionJob $job)
    {
        $job->load(['bom', 'karigar', 'branch', 'issuedPurity', 'receivedPurity']);
        return view('production.jobs.show', compact('job'));
    }

    public function jobIssue(Request $request, ProductionJob $job)
    {
        $data = $request->validate([
            'metal_weight_issued' => 'required|numeric|min:0.001',
            'issued_purity_id'    => 'required|exists:purity_levels,id',
        ]);

        $job->issueJob($data['metal_weight_issued'], $data['issued_purity_id']);
        return redirect()->back()->with('success', 'Metal issued to karigar.');
    }

    public function jobReceive(Request $request, ProductionJob $job)
    {
        $data = $request->validate([
            'metal_weight_received' => 'required|numeric|min:0.001',
            'received_purity_id'    => 'required|exists:purity_levels,id',
            'wastage_actual'        => 'required|numeric|min:0',
            'other_charges'         => 'nullable|numeric|min:0',
        ]);

        $job->receiveJob($data['metal_weight_received'], $data['received_purity_id'], $data['wastage_actual'], $data['other_charges'] ?? 0);
        return redirect()->back()->with('success', 'Job received and settlement created.');
    }

    // ==================== KARIGAR SETTLEMENTS ====================

    public function settlementIndex()
    {
        $settlements = KarigarSettlement::with(['karigar', 'branch'])->latest()->paginate(20);
        $stats = [
            'pending' => KarigarSettlement::where('payment_status', 'pending')->count(),
            'paid'    => KarigarSettlement::where('payment_status', 'paid')->count(),
            'total_pending_amount' => KarigarSettlement::where('payment_status', 'pending')->sum('total_amount'),
        ];
        return view('production.settlements.index', compact('settlements', 'stats'));
    }

    public function settlementCreate()
    {
        $karigars = Supplier::where('supplier_type', 'Karigar')->where('status', 'active')->get();
        $branches = Branch::where('is_active', true)->get();
        $goldRate = GoldRate::latest('date')->first();
        return view('production.settlements.create', compact('karigars', 'branches', 'goldRate'));
    }

    public function settlementStore(Request $request)
    {
        $data = $request->validate([
            'supplier_id'      => 'required|exists:suppliers,id',
            'branch_id'        => 'required|exists:branches,id',
            'date'             => 'required|date',
            'metal_type'       => 'required|in:gold,silver',
            'fine_weight_fixed'=> 'required|numeric|min:0',
            'fixed_rate'       => 'required|numeric|min:0',
            'labor_amount'     => 'nullable|numeric|min:0',
            'other_charges'    => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        $data['metal_value']        = $data['fine_weight_fixed'] * $data['fixed_rate'];
        $data['total_amount']       = $data['metal_value'] + ($data['labor_amount'] ?? 0) + ($data['other_charges'] ?? 0);
        $data['payment_status']     = 'pending';
        $data['created_by']         = Auth::id();
        $data['settlement_number']  = 'KS-' . date('Ymd') . '-' . str_pad(KarigarSettlement::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        KarigarSettlement::create($data);
        return redirect()->route('production.settlements.index')->with('success', 'Settlement created.');
    }

    public function settlementShow(KarigarSettlement $settlement)
    {
        $settlement->load(['karigar', 'branch', 'creator']);
        return view('production.settlements.show', compact('settlement'));
    }

    public function settlementPay(KarigarSettlement $settlement)
    {
        $settlement->update(['payment_status' => 'paid']);
        return redirect()->back()->with('success', 'Settlement marked as paid.');
    }

    // ==================== REFINERY ====================

    public function refineryIndex()
    {
        $batches = RefineryBatch::with(['refiner', 'branch'])->latest()->paginate(20);
        $stats = [
            'pending'  => RefineryBatch::where('status', 'pending')->count(),
            'sent'     => RefineryBatch::where('status', 'sent_to_refinery')->count(),
            'received' => RefineryBatch::where('status', 'received')->count(),
            'total_sent_weight' => RefineryBatch::sum('total_gross_weight_sent'),
        ];
        return view('production.refinery.index', compact('batches', 'stats'));
    }

    public function refineryCreate()
    {
        $refiners = Supplier::where('status', 'active')->get();
        $branches = Branch::where('is_active', true)->get();
        $pendingBuybacks = \App\Models\Buyback::where('status', 'completed')->whereNull('refinery_batch_id')->get();
        return view('production.refinery.create', compact('refiners', 'branches', 'pendingBuybacks'));
    }

    public function refineryStore(Request $request)
    {
        $data = $request->validate([
            'branch_id'                  => 'required|exists:branches,id',
            'refiner_id'                 => 'nullable|exists:suppliers,id',
            'total_gross_weight_sent'    => 'required|numeric|min:0.001',
            'estimated_fine_weight_sent' => 'required|numeric|min:0.001',
            'sent_date'                  => 'required|date',
            'buyback_ids'                => 'nullable|array',
        ]);

        DB::transaction(function () use ($data, $request) {
            $data['batch_number'] = 'REF-' . date('Ymd') . '-' . str_pad(RefineryBatch::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            $data['status']       = 'sent_to_refinery';
            $data['created_by']   = Auth::id();
            $buybackIds = $data['buyback_ids'] ?? [];
            unset($data['buyback_ids']);

            $batch = RefineryBatch::create($data);
            if ($buybackIds) {
                \App\Models\Buyback::whereIn('id', $buybackIds)->update(['refinery_batch_id' => $batch->id, 'status' => 'sent_to_refinery']);
            }
        });

        return redirect()->route('production.refinery.index')->with('success', 'Refinery batch created.');
    }

    public function refineryShow(RefineryBatch $batch)
    {
        $batch->load(['refiner', 'branch', 'buybacks.customer']);
        return view('production.refinery.show', compact('batch'));
    }

    public function refineryReceive(Request $request, RefineryBatch $batch)
    {
        $data = $request->validate([
            'actual_gross_weight_received' => 'required|numeric|min:0',
            'actual_fine_weight_received'  => 'required|numeric|min:0',
            'refining_charges'             => 'nullable|numeric|min:0',
        ]);

        $batch->receiveRefined($data['actual_gross_weight_received'], $data['actual_fine_weight_received'], $data['refining_charges'] ?? 0);
        return redirect()->back()->with('success', 'Refinery batch received and reconciled.');
    }
}
