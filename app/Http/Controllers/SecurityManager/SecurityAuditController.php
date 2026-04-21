<?php

namespace App\Http\Controllers\SecurityManager;

use App\Http\Controllers\Controller;
use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityAuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', SecurityAuditLog::class);

        $query = SecurityAuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest('created_at')->paginate(25);

        $categories = SecurityAuditLog::distinct('category')->pluck('category');
        $actions = SecurityAuditLog::distinct('action')->pluck('action');

        return view('security-manager.audit.index', [
            'logs' => $logs,
            'categories' => $categories,
            'actions' => $actions,
            'filters' => $request->only(['user_id', 'action', 'category', 'date_from', 'date_to']),
        ]);
    }

    public function show(SecurityAuditLog $log)
    {
        $this->authorize('view', $log);

        return view('security-manager.audit.show', compact('log'));
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', SecurityAuditLog::class);

        $query = SecurityAuditLog::with('user');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest('created_at')->get();

        $csv = "User,Action,Category,Subject,Description,IP Address,Date\n";

        foreach ($logs as $log) {
            $userName = $log->user ? $log->user->name : 'System';
            $csv .= "\"{$userName}\",";
            $csv .= "\"{$log->action}\",";
            $csv .= "\"{$log->category}\",";
            $csv .= "\"{$log->subject}\",";
            $csv .= '"'.str_replace('"', '""', $log->description).'",';
            $csv .= "\"{$log->ip_address}\",";
            $csv .= "\"{$log->created_at->format('Y-m-d H:i:s')}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="security-audit-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    public function loginHistory(Request $request)
    {
        $this->authorize('viewAny', SecurityAuditLog::class);

        $query = SecurityAuditLog::where('action', 'login')->with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest('created_at')->paginate(25);

        $dailyLogins = DB::table('security_audit_logs')
            ->where('action', 'login')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get();

        return view('security-manager.audit.login-history', [
            'logs' => $logs,
            'dailyLogins' => $dailyLogins,
        ]);
    }

    public function failedAttempts(Request $request)
    {
        $this->authorize('viewAny', SecurityAuditLog::class);

        $logs = SecurityAuditLog::where('action', 'login_failed')
            ->with('user')
            ->latest('created_at')
            ->paginate(25);

        return view('security-manager.audit.failed-attempts', compact('logs'));
    }
}
