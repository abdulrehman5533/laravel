<?php

namespace App\Http\Controllers\Girvi;

use App\Http\Controllers\Controller;
use App\Models\Girvi;
use App\Models\GirviPartialRelease;
use App\Models\GirviTopup;
use App\Models\GirviAuction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GirviAdvancedController extends Controller
{
    public function partialRelease(Request $request, Girvi $girvi)
    {
        $validated = $request->validate([
            'release_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:1',
            'items_to_release' => 'required|array|min:1',
            'items_to_release.*' => 'exists:girvi_items,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Calculate principal and interest split
            $interestDue = $girvi->interest_accrued - $girvi->interest_paid;
            $interestComponent = min($validated['amount_paid'], $interestDue);
            $principalComponent = $validated['amount_paid'] - $interestComponent;

            // Create partial release record
            $release = GirviPartialRelease::create([
                'girvi_id' => $girvi->id,
                'release_date' => $validated['release_date'],
                'amount_paid' => $validated['amount_paid'],
                'principal_component' => $principalComponent,
                'interest_component' => $interestComponent,
                'items_released' => $validated['items_to_release'],
                'released_by' => auth()->id(),
                'notes' => $validated['notes'],
            ]);

            // Mark items as released
            $girvi->items()->whereIn('id', $validated['items_to_release'])
                ->update(['status' => 'released']);

            // Update loan balances
            $girvi->principal_paid += $principalComponent;
            $girvi->interest_paid += $interestComponent;
            $girvi->outstanding_amount = ($girvi->loan_amount - $girvi->principal_paid) + 
                                        ($girvi->interest_accrued - $girvi->interest_paid);
            
            // Check if all items released
            $remainingItems = $girvi->items()->where('status', '!=', 'released')->count();
            if ($remainingItems == 0 && $girvi->outstanding_amount <= 0) {
                $girvi->status = 'settled';
            }
            
            $girvi->save();

            DB::commit();
            return back()->with('success', 'Partial release completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Partial release failed: ' . $e->getMessage());
        }
    }

    public function topup(Request $request, Girvi $girvi)
    {
        $validated = $request->validate([
            'topup_date' => 'required|date',
            'topup_amount' => 'required|numeric|min:1',
            'new_interest_rate' => 'nullable|numeric|min:0',
            'new_maturity_date' => 'nullable|date|after:today',
            'additional_items' => 'nullable|array',
            'additional_items.*.item_name' => 'required_with:additional_items|string',
            'additional_items.*.item_type' => 'required_with:additional_items|in:Gold,Silver,Diamond',
            'additional_items.*.gross_weight' => 'required_with:additional_items|numeric',
            'additional_items.*.net_weight' => 'required_with:additional_items|numeric',
            'additional_items.*.purity' => 'required_with:additional_items|string',
            'additional_items.*.estimated_value' => 'required_with:additional_items|numeric',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create topup record
            $topup = GirviTopup::create([
                'girvi_id' => $girvi->id,
                'topup_date' => $validated['topup_date'],
                'topup_amount' => $validated['topup_amount'],
                'new_interest_rate' => $validated['new_interest_rate'] ?? null,
                'new_maturity_date' => $validated['new_maturity_date'] ?? null,
                'additional_items' => $validated['additional_items'] ?? null,
                'approved_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Add new items if provided
            if (!empty($validated['additional_items'])) {
                foreach ($validated['additional_items'] as $itemData) {
                    $girvi->items()->create($itemData);
                }
            }

            // Update loan
            $girvi->loan_amount += $validated['topup_amount'];
            $girvi->outstanding_amount += $validated['topup_amount'];
            
            if ($validated['new_interest_rate']) {
                $girvi->interest_rate = $validated['new_interest_rate'];
            }
            
            if ($validated['new_maturity_date']) {
                $girvi->maturity_date = $validated['new_maturity_date'];
            }
            
            $girvi->save();

            DB::commit();
            return back()->with('success', 'Top-up completed successfully. New loan amount: Rs.' . number_format($girvi->loan_amount, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Top-up failed: ' . $e->getMessage());
        }
    }

    public function scheduleAuction(Request $request, Girvi $girvi)
    {
        $validated = $request->validate([
            'auction_date' => 'required|date|after:today',
            'reserve_price' => 'required|numeric|min:0',
            'auction_notes' => 'nullable|string',
        ]);

        $auctionNumber = 'AUC-' . now()->format('Ymd') . '-' . str_pad($girvi->id, 5, '0', STR_PAD_LEFT);

        $auction = GirviAuction::create([
            'girvi_id' => $girvi->id,
            'auction_number' => $auctionNumber,
            'auction_date' => $validated['auction_date'],
            'auction_status' => 'scheduled',
            'reserve_price' => $validated['reserve_price'],
            'auction_notes' => $validated['auction_notes'],
        ]);

        $girvi->status = 'auctioned';
        $girvi->save();

        return back()->with('success', "Auction scheduled: {$auctionNumber} on " . $auction->auction_date->format('d M Y'));
    }

    public function completeAuction(Request $request, GirviAuction $auction)
    {
        $validated = $request->validate([
            'winning_bid' => 'required|numeric|min:0',
            'winner_name' => 'required|string|max:255',
            'winner_contact' => 'required|string|max:20',
            'auction_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $auction->update([
                'auction_status' => 'completed',
                'winning_bid' => $validated['winning_bid'],
                'winner_name' => $validated['winner_name'],
                'winner_contact' => $validated['winner_contact'],
                'auction_notes' => $validated['auction_notes'] ?? $auction->auction_notes,
            ]);

            $girvi = $auction->girvi;
            
            // If winning bid covers outstanding, settle the loan
            if ($validated['winning_bid'] >= $girvi->outstanding_amount) {
                $girvi->status = 'settled';
                $girvi->principal_paid = $girvi->loan_amount;
                $girvi->interest_paid = $girvi->interest_accrued;
                $girvi->outstanding_amount = 0;
                $girvi->save();
            }

            DB::commit();
            return back()->with('success', 'Auction completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Auction completion failed: ' . $e->getMessage());
        }
    }

    public function cancelAuction(GirviAuction $auction)
    {
        $auction->update(['auction_status' => 'cancelled']);
        
        $girvi = $auction->girvi;
        $girvi->status = 'active';
        $girvi->save();

        return back()->with('success', 'Auction cancelled');
    }
}
