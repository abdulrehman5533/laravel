<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ProductionJob extends Model
{
    use BelongsToTenant, HasAuditLog;
    use SoftDeletes;

    protected $fillable = [
        'job_number', 'product_bom_id', 'karigar_id', 'employee_id', 'karigar_type', 'branch_id', 'status',
        'metal_weight_issued', 'issued_purity_id', 'fine_weight_issued',
        'metal_weight_received', 'received_purity_id', 'fine_weight_received',
        'wastage_allowed', 'wastage_actual', 'gold_loss_weight',
        'labor_charges', 'labor_rate_per_gram', 'labor_rate_per_piece', 'other_charges', 'total_job_cost',
        'issue_date', 'expected_delivery_date', 'actual_delivery_date',
        'notes', 'created_by',
    ];

    protected $casts = [
        'metal_weight_issued' => 'decimal:4',
        'fine_weight_issued' => 'decimal:4',
        'metal_weight_received' => 'decimal:4',
        'fine_weight_received' => 'decimal:4',
        'wastage_allowed' => 'decimal:4',
        'wastage_actual' => 'decimal:4',
        'gold_loss_weight' => 'decimal:4',
        'labor_charges' => 'decimal:2',
        'labor_rate_per_gram' => 'decimal:4',
        'labor_rate_per_piece' => 'decimal:4',
        'other_charges' => 'decimal:2',
        'total_job_cost' => 'decimal:2',
        'issue_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(ProductBom::class, 'product_bom_id');
    }

    public function karigar(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'karigar_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductionJobItem::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function issuedPurity(): BelongsTo
    {
        return $this->belongsTo(PurityLevel::class, 'issued_purity_id');
    }

    public function receivedPurity(): BelongsTo
    {
        return $this->belongsTo(PurityLevel::class, 'received_purity_id');
    }

    /**
     * Enterprise Logic: Issue metal to Karigar
     */
    public function issueJob(float $weight, int $purityId)
    {
        return DB::transaction(function () use ($weight, $purityId) {
            $purity = PurityLevel::findOrFail($purityId);
            $fineWeight = ($weight * $purity->percentage) / 100;

            $this->update([
                'status' => 'issued',
                'metal_weight_issued' => $weight,
                'issued_purity_id' => $purityId,
                'fine_weight_issued' => $fineWeight,
                'issue_date' => now(),
            ]);

            if ($this->karigar_type === 'external' && $this->karigar_id) {
                // Update Karigar (Supplier) Metal Ledger
                $this->karigar->current_gold_balance += $weight;
                $this->karigar->current_fine_gold_balance += $fineWeight;
                $this->karigar->save();

                // Record in Supplier Ledger Entry
                SupplierLedgerEntry::create([
                    'supplier_id' => $this->karigar_id,
                    'branch_id' => $this->branch_id,
                    'transaction_type' => 'metal_issue',
                    'reference_id' => $this->id,
                    'reference_type' => 'ProductionJob',
                    'metal_weight' => $weight,
                    'fine_weight' => $fineWeight,
                    'description' => "Metal issued for Job #{$this->job_number}",
                ]);
            }

            return $this;
        });
    }

    /**
     * Enterprise Logic: Receive finished goods and reconcile
     */
    public function receiveJob(float $receivedWeight, int $purityId, float $wastageActual, float $otherCharges = 0)
    {
        return DB::transaction(function () use ($receivedWeight, $purityId, $wastageActual, $otherCharges) {
            $purity = PurityLevel::findOrFail($purityId);
            $fineWeightReceived = ($receivedWeight * $purity->percentage) / 100;

            // Calculate Labor Charges if rates are set
            $laborCharges = 0;
            if ($this->labor_rate_per_gram > 0) {
                $laborCharges = $receivedWeight * $this->labor_rate_per_gram;
            } elseif ($this->labor_rate_per_piece > 0) {
                $laborCharges = $this->labor_rate_per_piece;
            }

            $totalCost = $laborCharges + $otherCharges;

            $this->update([
                'status' => 'completed',
                'metal_weight_received' => $receivedWeight,
                'received_purity_id' => $purityId,
                'fine_weight_received' => $fineWeightReceived,
                'wastage_actual' => $wastageActual,
                'labor_charges' => $laborCharges,
                'other_charges' => $otherCharges,
                'total_job_cost' => $totalCost,
                'actual_delivery_date' => now(),
            ]);

            if ($this->karigar_type === 'external' && $this->karigar_id) {
                // Update Karigar balances (Subtracting what they returned)
                $this->karigar->current_gold_balance -= $receivedWeight;
                $this->karigar->current_fine_gold_balance -= $fineWeightReceived;

                // Handle wastage from karigar balance
                $this->karigar->current_fine_gold_balance -= $wastageActual;

                $this->karigar->save();

                // Record settlement entry
                KarigarSettlement::create([
                    'tenant_id' => $this->tenant_id,
                    'karigar_id' => $this->karigar_id,
                    'production_job_id' => $this->id,
                    'total_labor_charges' => $laborCharges,
                    'total_wastage_fine' => $wastageActual, // wastage in fine weight
                    'status' => 'pending',
                ]);
            }

            return $this;
        });
    }
}
