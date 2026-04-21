<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::paginate(10);
        return view('hr.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('hr.shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'grace_period_minutes' => 'nullable|integer|min:0',
            'is_night_shift' => 'boolean',
            'color_code' => 'nullable|string|max:7',
        ]);

        Shift::create($validated);

        return redirect()->route('hr.shifts.index')->with('success', 'Shift created successfully.');
    }

    public function edit(Shift $shift)
    {
        return view('hr.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'grace_period_minutes' => 'nullable|integer|min:0',
            'is_night_shift' => 'boolean',
            'color_code' => 'nullable|string|max:7',
        ]);

        $shift->update($validated);

        return redirect()->route('hr.shifts.index')->with('success', 'Shift updated successfully.');
    }

    public function destroy(Shift $shift)
    {
        if ($shift->employeeShifts()->count() > 0) {
            return back()->with('error', 'Cannot delete shift as it is assigned to employees.');
        }

        $shift->delete();

        return redirect()->route('hr.shifts.index')->with('success', 'Shift deleted successfully.');
    }
}
