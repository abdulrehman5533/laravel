<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\HR\Candidate;
use App\Models\HR\EmployeeOnboarding;
use App\Models\HR\EmployeeOnboardingTask;
use App\Models\HR\Interview;
use App\Models\HR\JobPosting;
use App\Models\HR\OnboardingChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecruitmentController extends Controller
{
    public function index()
    {
        $jobPostings = JobPosting::withCount('candidates')->get();

        return view('hr.recruitment.index', compact('jobPostings'));
    }

    public function createJob()
    {
        $branches = Branch::all();

        return view('hr.recruitment.jobs.create', compact('branches'));
    }

    public function storeJob(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'department' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'status' => 'required|in:draft,open,closed,on_hold',
            'closing_date' => 'nullable|date',
        ]);

        JobPosting::create($validated);

        return redirect()->route('hr.recruitment.index')->with('success', 'Job posting created successfully.');
    }

    public function showJob(JobPosting $job)
    {
        $job->load('candidates.interviews.feedback');

        return view('hr.recruitment.jobs.show', compact('job'));
    }

    public function apply(Request $request, JobPosting $job)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'expected_salary' => 'nullable|numeric',
        ]);

        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes');
            $validated['resume_path'] = $path;
        }

        $job->candidates()->create($validated);

        return back()->with('success', 'Application submitted successfully.');
    }

    public function scheduleInterview(Request $request, Candidate $candidate)
    {
        $validated = $request->validate([
            'interviewer_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'location' => 'nullable|string',
        ]);

        $candidate->interviews()->create($validated);
        $candidate->update(['status' => 'interviewing']);

        return back()->with('success', 'Interview scheduled successfully.');
    }

    public function submitFeedback(Request $request, Interview $interview)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'required|string',
            'recommendation' => 'required|in:hire,hold,reject',
        ]);

        $validated['interviewer_id'] = auth()->id();
        $interview->feedback()->create($validated);

        return back()->with('success', 'Feedback submitted successfully.');
    }

    public function hire(Candidate $candidate)
    {
        if ($candidate->status === 'hired') {
            return back()->with('error', 'Candidate is already hired.');
        }

        DB::transaction(function () use ($candidate) {
            $candidate->update(['status' => 'hired']);

            // Create Employee record
            $employee = Employee::create([
                'first_name' => $candidate->first_name,
                'last_name' => $candidate->last_name,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'department' => $candidate->jobPosting->department,
                'branch_id' => $candidate->jobPosting->branch_id,
                'base_salary' => $candidate->expected_salary ?? 0,
                'joining_date' => now(),
                'status' => 'active',
                'onboarding_status' => 'pending',
            ]);

            // Trigger Onboarding if a default checklist exists
            $checklist = OnboardingChecklist::first();
            if ($checklist) {
                $onboarding = EmployeeOnboarding::create([
                    'employee_id' => $employee->id,
                    'checklist_id' => $checklist->id,
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);

                foreach ($checklist->tasks as $task) {
                    EmployeeOnboardingTask::create([
                        'onboarding_id' => $onboarding->id,
                        'task_id' => $task->id,
                        'is_completed' => false,
                    ]);
                }
            }
        });

        return back()->with('success', 'Candidate hired and onboarding initiated.');
    }
}
