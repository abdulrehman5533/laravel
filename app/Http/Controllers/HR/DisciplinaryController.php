<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\DisciplinaryAction;
use Illuminate\Http\Request;

class DisciplinaryController extends Controller
{
    public function index()
    {
        $actions = DisciplinaryAction::with('employee')->orderBy('incident_date', 'desc')->get();

        return view('hr.disciplinary.index', compact('actions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'action_type' => 'required|string',
            'incident_date' => 'required|date',
            'reason' => 'required|string',
            'action_taken' => 'required|string',
            'status' => 'required|in:pending,appealed,finalized',
        ]);

        $validated['recorded_by'] = auth()->id();

        DisciplinaryAction::create($validated);

        return redirect()->back()->with('success', 'Disciplinary action recorded successfully.');
    }

    public function update(Request $request, DisciplinaryAction $action)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,appealed,finalized',
            'action_taken' => 'nullable|string',
        ]);

        $action->update($validated);

        return redirect()->back()->with('success', 'Disciplinary action updated successfully.');
    }
}
