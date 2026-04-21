<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowApproval;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function index()
    {
        $workflows = Workflow::all();

        return view('workflow.index', compact('workflows'));
    }

    public function myApprovals()
    {
        $approvals = WorkflowApproval::with(['workflow', 'model'])
            ->where('approver_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        return view('workflow.approvals', compact('approvals'));
    }

    public function approve(Request $request, $id)
    {
        $approval = WorkflowApproval::findOrFail($id);
        $this->workflowService->processApproval($approval, 'approved', $request->comments);

        return redirect()->back()->with('success', 'Approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $approval = WorkflowApproval::findOrFail($id);
        $this->workflowService->processApproval($approval, 'rejected', $request->comments);

        return redirect()->back()->with('success', 'Rejected successfully.');
    }
}
