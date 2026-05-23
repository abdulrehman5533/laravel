<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CrmInteraction;
use App\Models\Customer;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    public function index(Request $request)
    {
        $query = CrmInteraction::with('customer');

        if ($request->type)   $query->where('interaction_type', $request->type);
        if ($request->status) $query->where('status', $request->status);
        if ($request->from)   $query->whereDate('interaction_date', '>=', $request->from);
        if ($request->to)     $query->whereDate('interaction_date', '<=', $request->to);

        $interactions = $query->latest('interaction_date')->paginate(20);

        $stats = [
            'total'      => CrmInteraction::count(),
            'open'       => CrmInteraction::where('status', 'open')->count(),
            'follow_ups' => CrmInteraction::whereNotNull('follow_up_date')->where('follow_up_date', '<=', now())->where('status', '!=', 'closed')->count(),
        ];

        return view('crm.interactions.index', compact('interactions', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'crm_customer_id'  => 'required|exists:customers,id',
            'interaction_type' => 'required|in:call,visit,whatsapp,email,sms,complaint,inquiry,feedback',
            'description'      => 'required|string|max:1000',
            'sentiment'        => 'nullable|in:positive,neutral,negative',
            'interaction_date' => 'required|date',
            'follow_up_date'   => 'nullable|date|after:today',
            'follow_up_notes'  => 'nullable|string|max:500',
        ]);

        $data['status']     = 'open';
        $data['created_by'] = auth()->id();

        CrmInteraction::create($data);

        return redirect()->back()->with('success', 'Interaction recorded successfully.');
    }

    public function addFollowUp(Request $request, CrmInteraction $interaction)
    {
        $data = $request->validate([
            'follow_up_date'  => 'required|date',
            'follow_up_notes' => 'nullable|string|max:500',
        ]);

        $interaction->update($data);
        return back()->with('success', 'Follow-up scheduled.');
    }

    public function close(CrmInteraction $interaction)
    {
        $interaction->update(['status' => 'closed']);
        return back()->with('success', 'Interaction closed.');
    }
}
