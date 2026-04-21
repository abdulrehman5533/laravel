<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowApproval;
use App\Models\WorkflowStep;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class WorkflowService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Start a workflow for a given model.
     */
    public function startWorkflow(Model $model, string $module)
    {
        $workflow = Workflow::where('module', $module)->where('is_active', true)->first();
        if (! $workflow) {
            return null;
        }

        return $this->moveToNextStep($model, $workflow, 0);
    }

    /**
     * Move to the next valid step in the workflow.
     */
    public function moveToNextStep(Model $model, Workflow $workflow, int $currentOrder)
    {
        $nextStep = WorkflowStep::where('workflow_id', $workflow->id)
            ->where('step_order', '>', $currentOrder)
            ->orderBy('step_order')
            ->get()
            ->first(function ($step) use ($model) {
                return $this->checkCondition($model, $step);
            });

        if (! $nextStep) {
            // No more steps, mark as fully approved if the model supports it
            if (method_exists($model, 'markAsApproved')) {
                $model->markAsApproved();
            }

            return null;
        }

        $approverId = $this->resolveApprover($model, $nextStep);

        $approval = WorkflowApproval::create([
            'workflow_id' => $workflow->id,
            'step_id' => $nextStep->id,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'current_step' => $nextStep->step_order,
            'approver_id' => $approverId,
            'status' => 'pending',
            'sla_due_at' => Carbon::now()->addHours($nextStep->sla_hours),
        ]);

        $this->notifyApprover($approval);

        return $approval;
    }

    /**
     * Process an approval action.
     */
    public function approve(WorkflowApproval $approval, ?string $comments = null)
    {
        $approval->update([
            'status' => 'approved',
            'actioned_by' => Auth::id(),
            'actioned_at' => Carbon::now(),
            'comments' => $comments,
        ]);

        $this->logAudit($approval, 'approved');

        return $this->moveToNextStep($approval->model, $approval->workflow, $approval->current_step);
    }

    /**
     * Process a rejection action.
     */
    public function reject(WorkflowApproval $approval, string $reason)
    {
        $approval->update([
            'status' => 'rejected',
            'actioned_by' => Auth::id(),
            'actioned_at' => Carbon::now(),
            'rejection_reason' => $reason,
        ]);

        if (method_exists($approval->model, 'markAsRejected')) {
            $approval->model->markAsRejected($reason);
        }

        $this->logAudit($approval, 'rejected');

        return $approval;
    }

    /**
     * Resolve who should approve this step.
     */
    protected function resolveApprover(Model $model, WorkflowStep $step)
    {
        return match ($step->approver_type) {
            'user' => $step->approver_id,
            'role' => User::where('role_id', $step->approver_id)->first()?->id, // Simplified: gets first user with role
            'direct_manager' => $model->creator?->manager_id ?? $model->employee?->manager_id,
            'department' => User::where('department_id', $step->approver_id)->first()?->id,
            default => null,
        };
    }

    /**
     * Check if the step condition is met.
     */
    protected function checkCondition(Model $model, WorkflowStep $step)
    {
        if ($step->condition_type === 'none') {
            return true;
        }

        if ($step->condition_type === 'amount_greater_than') {
            $amount = $model->total_amount ?? $model->grand_total ?? $model->amount ?? 0;

            return $amount > (float) $step->condition_value;
        }

        // Add more conditions as needed (e.g., category_is)

        return true;
    }

    /**
     * Notify the approver.
     */
    protected function notifyApprover(WorkflowApproval $approval)
    {
        $approver = User::find($approval->approver_id);
        if (! $approver) {
            return;
        }

        $this->notificationService->notify('workflow_pending_approval', $approver, [
            'workflow_name' => $approval->workflow->name,
            'model_type' => class_basename($approval->model_type),
            'sla_due' => $approval->sla_due_at->format('M d, Y H:i'),
        ]);
    }

    /**
     * Log the audit trail.
     */
    protected function logAudit(WorkflowApproval $approval, string $action)
    {
        $trail = $approval->audit_trail ?? [];
        $trail[] = [
            'action' => $action,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'timestamp' => Carbon::now()->toDateTimeString(),
            'comments' => $approval->comments ?? $approval->rejection_reason,
        ];
        $approval->update(['audit_trail' => $trail]);
    }
}
