<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InteractionController extends Controller
{
    /**
     * Display all interactions
     */
    public function index(): View
    {
        $interactions = \App\Models\CrmInteraction::with('customer')
            ->latest()
            ->paginate(15);

        return view('crm.interactions.index', compact('interactions'));
    }

    /**
     * Add follow-up to interaction
     */
    public function addFollowUp(Request $request, $interaction)
    {
        $validated = $request->validate([
            'follow_up_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Implementation for adding follow-up

        return back()->with('success', 'Follow-up added successfully');
    }
}
