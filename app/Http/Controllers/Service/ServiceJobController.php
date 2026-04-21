<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\ServiceJob;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceJobController extends Controller
{
    /**
     * Display a listing of service jobs
     */
    public function index(): View
    {
        $jobs = ServiceJob::with('customer', 'assignee')
            ->latest()
            ->paginate(15);

        return view('service.jobs.index', compact('jobs'));
    }

    /**
     * Show the form for creating a new job
     */
    public function create(): View
    {
        $customers = \App\Models\Customer::all();
        $karigars = \App\Models\Supplier::where('supplier_type', 'Karigar')
            ->orWhere('supplier_type', 'like', '%Karigar%')
            ->active()
            ->get();

        // Get users with staff role using the role relationship
        $staff = \App\Models\User::whereHas('role', function ($query) {
            $query->where('slug', 'staff')
                ->orWhere('slug', 'technician')
                ->orWhere('slug', 'employee');
        })->where('is_active', true)->get();

        // If no staff found, get all active users as fallback
        if ($staff->isEmpty()) {
            $staff = \App\Models\User::where('is_active', true)->get();
        }

        return view('service.jobs.create', compact('customers', 'staff', 'karigars'));
    }

    /**
     * Store a newly created job
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'karigar_id' => 'nullable|exists:suppliers,id',
            'service_type' => 'nullable|string',
            'job_type' => 'nullable|string',
            'priority' => 'nullable|string',
            'item_type' => 'nullable|string',
            'item_weight' => 'nullable|numeric',
            'item_description' => 'nullable|string',
            'description' => 'required|string',
            'expected_completion_date' => 'required|date',
            'estimated_charge' => 'required|numeric',
            'special_instructions' => 'nullable|string',
            'karigar_instructions' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'is_urgent' => 'boolean',
            'before_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'items' => 'nullable|array',
            'items.*.ornament_name' => 'required|string',
            'items.*.metal_type' => 'required|string',
            'items.*.purity_expected' => 'nullable|numeric',
            'items.*.weight_issued' => 'nullable|numeric',
            'items.*.wastage_category_id' => 'nullable|exists:service_category_wastages,id',
        ]);

        // Map 'description' from form to 'issue_description' in database
        $validated['issue_description'] = $validated['description'];
        unset($validated['description']);

        $validated['is_urgent'] = $request->boolean('is_urgent', false);

        // Handle photos if any
        if ($request->hasFile('before_photos')) {
            $photos = [];
            foreach ($request->file('before_photos') as $photo) {
                $photos[] = $photo->store('service-jobs/before', 'public');
            }
            $validated['before_photos'] = $photos;
        }

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $job = ServiceJob::create($validated);

        if (! empty($items)) {
            foreach ($items as $item) {
                $jobItem = $job->items()->create($item);

                // Update Karigar Ledger if weight issued
                if ($job->karigar_id && $jobItem->weight_issued > 0) {
                    $this->updateKarigarMetalLedger($job->karigar, $jobItem, 'issue');
                }
            }
        } else {
            // Create a default item if none provided but single item info exists
            if ($request->item_type || $request->item_weight) {
                $jobItem = $job->items()->create([
                    'ornament_name' => $request->item_type ?? 'Main Item',
                    'metal_type' => 'Gold', // Default
                    'weight_issued' => $request->item_weight,
                    'status' => 'issued',
                ]);

                // Update Karigar Ledger
                if ($job->karigar_id && $jobItem->weight_issued > 0) {
                    $this->updateKarigarMetalLedger($job->karigar, $jobItem, 'issue');
                }
            }
        }

        return redirect()->route('service.jobs.show', $job)
            ->with('success', 'Service job created successfully');
    }

    public function receiveItems(Request $request, ServiceJob $job)
    {
        $validated = $request->validate([
            'received_items' => 'required|array',
            'received_items.*.id' => 'required|exists:service_job_items,id',
            'received_items.*.purity_received' => 'nullable|numeric',
            'received_items.*.weight_received' => 'nullable|numeric',
            'received_items.*.wastage_actual' => 'nullable|numeric',
            'received_items.*.labor_charge' => 'nullable|numeric',
        ]);

        foreach ($validated['received_items'] as $itemId => $data) {
            $item = \App\Models\ServiceJobItem::find($data['id']);
            if ($item && ($data['weight_received'] || $data['purity_received'])) {

                $isExcessWastage = false;
                $isPurityDiluted = false;
                $wastageLimitApplied = 0;

                if ($item->wastage_category_id) {
                    $category = \App\Models\ServiceCategoryWastage::find($item->wastage_category_id);
                    if ($category) {
                        $wastageLimitApplied = $category->calculateWastageLimit($item->weight_issued);
                        if (($data['wastage_actual'] ?? 0) > $wastageLimitApplied) {
                            $isExcessWastage = true;
                        }
                    }
                }

                // Check purity variance (Strict 0.1% tolerance for Tier-1)
                $purityVariance = ($item->purity_expected ?? 0) - ($data['purity_received'] ?? 0);
                if ($purityVariance > 0.10) {
                    $isPurityDiluted = true;
                }

                $item->update([
                    'purity_received' => $data['purity_received'],
                    'weight_received' => $data['weight_received'],
                    'wastage_actual' => $data['wastage_actual'],
                    'wastage_limit_applied' => $wastageLimitApplied,
                    'is_excess_wastage' => $isExcessWastage,
                    'labor_charge' => $data['labor_charge'],
                    'status' => 'received',
                    'is_approved' => (! $isExcessWastage && ! $isPurityDiluted), // Auto-approve only if BOTH clean
                    'quality_remarks' => $isPurityDiluted ? 'PURITY DILUTED: Variance of '.number_format($purityVariance, 2).'% detected.' : null,
                ]);

                // Update Karigar Ledger on receipt
                if ($job->karigar_id && $item->weight_received > 0 && $item->is_approved) {
                    $this->updateKarigarMetalLedger($job->karigar, $item, 'receipt');
                }
            }
        }

        // Update job status if all items received
        if ($job->items()->where('status', '!=', 'received')->where('status', '!=', 'approved')->count() == 0) {
            $job->update(['status' => 'completed']);
        }

        return back()->with('success', 'Ornaments received and approved successfully');
    }

    /**
     * Update Karigar Metal Ledger
     */
    protected function updateKarigarMetalLedger($karigar, $item, $type)
    {
        $ledger = $karigar->getLedgerForBranch(auth()->user()->branch_id ?? 1);

        $grossWeight = $type === 'issue' ? (float) $item->weight_issued : (float) $item->weight_received;

        // Tier-1 Rule: Fine weight must be absolute to the purity issued/received
        if ($type === 'issue') {
            $fineWeight = (float) $item->getFineWeightIssued();
        } else {
            // On receipt, we credit the Karigar for the fine gold they returned + allowed wastage
            // If they returned less fine gold (due to dilution), their debt remains open in Fine Gold
            $fineWeight = (float) ($item->weight_received * ($item->purity_received ?? 0)) / 100;
            $fineWeight += (float) ($item->wastage_allowed ?? 0);
        }

        $laborCharge = ($type === 'receipt') ? (float) ($item->labor_charge ?? 0) : 0;

        // Enterprise Ledger Logic:
        // Issue: Debit Karigar (He owes us Metal)
        // Receipt: Credit Karigar (He returned Metal, we owe him Labor)
        if ($type === 'issue') {
            $newGross = $ledger->current_gross_weight + $grossWeight;
            $newFine = $ledger->current_fine_weight + $fineWeight;
            $newBalance = $ledger->current_balance; // Metal issue doesn't change cash
            $entryType = 'debit';
        } else {
            $newGross = $ledger->current_gross_weight - $grossWeight;
            $newFine = $ledger->current_fine_weight - $fineWeight;
            $newBalance = $ledger->current_balance + $laborCharge; // We owe Karigar for labor (Credit)
            $entryType = 'credit';
        }

        \App\Models\SupplierLedgerEntry::create([
            'supplier_ledger_id' => $ledger->id,
            'date' => now(),
            'type' => $entryType,
            'amount' => $laborCharge,
            'gross_weight' => $grossWeight,
            'fine_weight' => $fineWeight,
            'reference_type' => 'ServiceJobItem',
            'reference_id' => $item->id,
            'running_balance' => $newBalance,
            'running_gross_weight' => $newGross,
            'running_fine_weight' => $newFine,
            'description' => ($type === 'issue' ? 'Metal Issued: ' : 'Metal Received: ').$item->ornament_name.($laborCharge > 0 ? " + Labor: {$laborCharge}" : '').' (Job #'.$item->serviceJob->job_number.')',
        ]);

        $ledger->update([
            'current_gross_weight' => $newGross,
            'current_fine_weight' => $newFine,
            'current_balance' => $newBalance,
            'transaction_count' => $ledger->transaction_count + 1,
            'last_transaction_date' => now(),
        ]);
    }

    /**
     * Display the specified job
     */
    public function show(ServiceJob $job): View
    {
        $job->load('customer', 'assignee', 'workflows');

        return view('service.jobs.show', compact('job'));
    }

    /**
     * Show the form for editing
     */
    public function edit(ServiceJob $job): View
    {
        $customers = \App\Models\Customer::all();
        // Get users with staff role using the role relationship
        $staff = \App\Models\User::whereHas('role', function ($query) {
            $query->where('slug', 'staff')
                ->orWhere('slug', 'technician')
                ->orWhere('slug', 'employee');
        })->where('is_active', true)->get();

        // If no staff found, get all active users as fallback
        if ($staff->isEmpty()) {
            $staff = \App\Models\User::where('is_active', true)->get();
        }

        return view('service.jobs.edit', compact('job', 'customers', 'staff'));
    }

    /**
     * Update the specified job
     */
    public function update(Request $request, ServiceJob $job)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_type' => 'nullable|string',
            'item_type' => 'nullable|string',
            'item_weight' => 'nullable|numeric',
            'item_description' => 'nullable|string',
            'description' => 'required|string',
            'expected_completion_date' => 'required|date',
            'estimated_charge' => 'required|numeric',
            'special_instructions' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'is_urgent' => 'boolean',
        ]);

        // Map 'description' from form to 'issue_description' in database
        $validated['issue_description'] = $validated['description'];
        unset($validated['description']);

        $validated['is_urgent'] = $request->boolean('is_urgent', false);

        $job->update($validated);

        return redirect()->route('service.jobs.show', $job)
            ->with('success', 'Service job updated successfully');
    }

    /**
     * Show workflow for job
     */
    public function workflow(ServiceJob $job): View
    {
        $job->load('workflowHistory');

        return view('service.jobs.workflow', compact('job'));
    }

    /**
     * Move job in workflow
     */
    public function moveWorkflow(Request $request, ServiceJob $job)
    {
        $validated = $request->validate([
            'new_status' => 'required|in:received,in_progress,completed,delivered,on_hold',
        ]);

        $job->update(['status' => $validated['new_status']]);

        return back()->with('success', 'Job status updated successfully');
    }

    /**
     * Assign job to staff member
     */
    public function assign(Request $request, ServiceJob $job)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $job->update($validated);

        return back()->with('success', 'Job assigned successfully');
    }

    /**
     * Upload photos for job
     */
    public function uploadPhotos(Request $request, ServiceJob $job)
    {
        $validated = $request->validate([
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photos')) {
            $existingPhotos = $job->before_photos ?? [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('service-jobs/before', 'public');
                $existingPhotos[] = $path;
            }
            $job->update(['before_photos' => $existingPhotos]);
        }

        return back()->with('success', 'Photos uploaded successfully');
    }

    /**
     * Mark job as completed
     */
    public function complete(Request $request, ServiceJob $job)
    {
        $job->update([
            'status' => 'completed',
            'actual_completion_date' => now(),
        ]);

        return back()->with('success', 'Job marked as completed');
    }

    /**
     * Deliver completed job
     */
    public function deliver(Request $request, ServiceJob $job)
    {
        $validated = $request->validate([
            'delivery_notes' => 'nullable|string',
        ]);

        $job->update([
            'status' => 'delivered',
            'delivery_date' => now(),
            'delivery_notes' => $validated['delivery_notes'] ?? null,
        ]);

        return back()->with('success', 'Job marked as delivered');
    }

    /**
     * Get overdue jobs
     */
    public function overdue(): View
    {
        $jobs = ServiceJob::overdue()->latest()->paginate(15);

        return view('service.jobs.overdue', compact('jobs'));
    }

    /**
     * Get urgent jobs
     */
    public function urgent(): View
    {
        // Use model scope which checks 'is_urgent' column
        $jobs = ServiceJob::urgent()->whereNotIn('status', ['completed', 'delivered'])->latest()->paginate(15);

        return view('service.jobs.urgent', compact('jobs'));
    }

    /**
     * Delete the specified job
     */
    public function destroy(ServiceJob $job)
    {
        $job->delete();

        return redirect()->route('service.jobs.index')
            ->with('success', 'Service job deleted successfully');
    }
}
