<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HR\EmployeeOnboarding;
use App\Models\HR\EmployeeOnboardingTask;
use App\Models\HR\OnboardingChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    public function index()
    {
        $onboardings = EmployeeOnboarding::with(['employee', 'checklist'])->get();

        return view('hr.onboarding.index', compact('onboardings'));
    }

    public function start(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'checklist_id' => 'required|exists:hr_onboarding_checklists,id',
        ]);

        DB::transaction(function () use ($request) {
            $onboarding = EmployeeOnboarding::create([
                'employee_id' => $request->employee_id,
                'checklist_id' => $request->checklist_id,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            $checklist = OnboardingChecklist::find($request->checklist_id);
            foreach ($checklist->tasks as $task) {
                EmployeeOnboardingTask::create([
                    'onboarding_id' => $onboarding->id,
                    'task_id' => $task->id,
                    'is_completed' => false,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Onboarding process started.');
    }

    public function completeTask(EmployeeOnboardingTask $task)
    {
        $task->update([
            'is_completed' => true,
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);

        // Check if all tasks are completed
        $onboarding = $task->onboarding;
        if ($onboarding->tasks()->where('is_completed', false)->count() === 0) {
            $onboarding->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Update employee onboarding status
            $onboarding->employee->update(['onboarding_status' => 'completed']);
        }

        return back()->with('success', 'Task marked as completed.');
    }
}
