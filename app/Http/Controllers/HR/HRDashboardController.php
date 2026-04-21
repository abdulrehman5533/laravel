<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HR\Asset;
use App\Models\HR\Candidate;
use App\Models\HR\DisciplinaryAction;
use App\Models\HR\EmployeeOnboarding;
use App\Models\HR\JobPosting;
use App\Models\HR\LeaveRequest;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class HRDashboardController extends Controller
{
    public function index()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $metrics = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'present_today' => Attendance::where('date', now()->toDateString())->where('status', 'present')->count(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')->count(),
            'total_payroll_last_month' => Payroll::where('month_year', now()->subMonth()->format('m-Y'))->sum('net_salary'),
            'open_jobs' => JobPosting::where('status', 'open')->count(),
            'pending_candidates' => Candidate::whereIn('status', ['applied', 'screening', 'interviewing'])->count(),
            'active_onboardings' => EmployeeOnboarding::where('status', 'in_progress')->count(),
            'assigned_assets' => Asset::where('status', 'assigned')->count(),
            'pending_disciplinary' => DisciplinaryAction::where('status', 'pending')->count(),
        ];

        // Advanced Analytics: Turnover Rate (Last 12 Months)
        $separations = DB::table('hr_lifecycle_events')
            ->whereIn('event_type', ['resignation', 'termination'])
            ->where('effective_date', '>=', now()->subYear())
            ->count();

        $avgEmployees = Employee::count(); // Simplified for now
        $metrics['turnover_rate'] = $avgEmployees > 0 ? round(($separations / $avgEmployees) * 100, 2) : 0;

        // Recruitment Funnel
        $recruitment_funnel = [
            'applied' => Candidate::where('status', 'applied')->count(),
            'interviewing' => Candidate::where('status', 'interviewing')->count(),
            'hired' => Candidate::where('status', 'hired')->count(),
            'rejected' => Candidate::where('status', 'rejected')->count(),
        ];

        $department_stats = Employee::select('department', DB::raw('count(*) as count'))
            ->groupBy('department')
            ->get();

        $branch_stats = Employee::with('branch')
            ->select('branch_id', DB::raw('count(*) as count'))
            ->groupBy('branch_id')
            ->get();

        $recent_activities = DB::table('hr_lifecycle_events')
            ->join('employees', 'hr_lifecycle_events.employee_id', '=', 'employees.id')
            ->select('hr_lifecycle_events.*', 'employees.first_name', 'employees.last_name')
            ->orderBy('hr_lifecycle_events.created_at', 'desc')
            ->limit(10)
            ->get();

        // Attendance Trends (Last 7 Days)
        $attendance_trends = Attendance::select('date', DB::raw('count(*) as count'))
            ->where('status', 'present')
            ->where('date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('hr.dashboard', compact(
            'metrics',
            'department_stats',
            'branch_stats',
            'recent_activities',
            'recruitment_funnel',
            'attendance_trends'
        ));
    }
}
