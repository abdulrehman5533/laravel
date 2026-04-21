<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class RefineryBatch extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = [
        'batch_number', 'branch_id', 'status', 'total_gross_weight_sent',
        'estimated_fine_weight_sent', 'actual_gross_weight_received',
        'actual_fine_weight_received', 'refining_loss_weight',
        'refining_charges', 'sent_date', 'received_date',
        'refiner_id', 'created_by',
    ];

    protected $casts = [
        'total_gross_weight_sent' => 'decimal:4',
        'estimated_fine_weight_sent' => 'decimal:4',
        'actual_gross_weight_received' => 'decimal:4',
        'actual_fine_weight_received' => 'decimal:4',
        'refining_loss_weight' => 'decimal:4',
        'refining_charges' => 'decimal:2',
        'sent_date' => 'date',
        'received_date' => 'date',
    ];

    public function buybacks(): HasMany
    {
        return $this->hasMany(Buyback::class);
    }

    public function refiner(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'refiner_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Enterprise Logic: Add Buybacks to this batch
     */
    public function addBuybacks(array $buybackIds)
    {
        return DB::transaction(function () use ($buybackIds) {
            $buybacks = Buyback::whereIn('id', $buybackIds)->where('status', 'completed')->get();

            foreach ($buybacks as $buyback) {
                $buyback->update([
                    'refinery_batch_id' => $this->id,
                    'status' => 'sent_to_refinery',
                ]);

                $this->total_gross_weight_sent += $buyback->net_weight;
                $this->estimated_fine_weight_sent += $buyback->net_fine_weight;
            }

            $this->status = 'sent_to_refinery';
            $this->sent_date = now();
            $this->save();

            return $this;
        });
    }

    /**
     * Enterprise Logic: Receive refined metal
     */
    public function receiveRefined(float $actualGross, float $actualFine, float $charges = 0)
    {
        return DB::transaction(function () use ($actualGross, $actualFine, $charges) {
            $loss = $this->estimated_fine_weight_sent - $actualFine;

            $this->update([
                'status' => 'received',
                'actual_gross_weight_received' => $actualGross,
                'actual_fine_weight_received' => $actualFine,
                'refining_loss_weight' => $loss,
                'refining_charges' => $charges,
                'received_date' => now(),
            ]);

            // Update Buybacks status
            $this->buybacks()->update(['status' => 'refined']);

            // Update Branch Metal Stock (Adding refined fine gold)
            // Here you would typically move this to 'Pure Gold' inventory

            return $this;
        });
    }
}
