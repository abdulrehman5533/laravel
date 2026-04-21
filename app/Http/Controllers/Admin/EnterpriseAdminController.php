<?php

/**
 * Enterprise Admin Dashboard Controller
 * Manages the high-level overview of enterprise modules.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Branch;
use App\Models\Document;
use App\Models\Employee;
use App\Models\WorkflowApproval;

class EnterpriseAdminController extends Controller
{
    /**
     * Display the Enterprise Admin dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'total_employees' => Employee::count(),
            'pending_approvals' => WorkflowApproval::where('status', 'pending')->count(),
            'total_documents' => Document::count(),
            'active_api_keys' => ApiKey::where('status', 'active')->count(),
            'total_branches' => Branch::count(),
            'recent_approvals' => WorkflowApproval::with(['workflow', 'approver'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
