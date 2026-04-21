<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Display service dashboard
     */
    public function dashboard(): View
    {
        $branchId = Auth::user()->branch_id;

        // Get service statistics
        $totalJobs = \App\Models\ServiceJob::when($branchId, function ($q) use ($branchId) {
            return $q->where('branch_id', $branchId);
        })->count();
        $pendingJobs = \App\Models\ServiceJob::when($branchId, function ($q) use ($branchId) {
            return $q->where('branch_id', $branchId);
        })
            ->whereIn('status', ['received', 'checking', 'workshop', 'polishing'])->count();
        $overdueJobs = \App\Models\ServiceJob::when($branchId, function ($q) use ($branchId) {
            return $q->where('branch_id', $branchId);
        })
            ->where('expected_completion_date', '<', now())
            ->whereNotIn('status', ['completed', 'delivered'])
            ->count();
        $completedToday = \App\Models\ServiceJob::when($branchId, function ($q) use ($branchId) {
            return $q->where('branch_id', $branchId);
        })
            ->whereDate('actual_completion_date', today())->count();

        $totalKarigars = \App\Models\Supplier::when($branchId, function ($q) use ($branchId) {
            return $q->where('branch_id', $branchId);
        })
            ->where('supplier_type', 'Karigar')->count();
        $pendingKarigarInvoices = \App\Models\KarigarInvoice::when($branchId, function ($q) use ($branchId) {
            return $q->whereHas('karigar', function ($sq) use ($branchId) {
                $sq->where('branch_id', $branchId);
            });
        })
            ->where('status', 'pending')->count();

        return view('service.dashboard', [
            'totalJobs' => $totalJobs,
            'pendingJobs' => $pendingJobs,
            'overdueJobs' => $overdueJobs,
            'completedToday' => $completedToday,
            'totalKarigars' => $totalKarigars,
            'pendingKarigarInvoices' => $pendingKarigarInvoices,
        ]);
    }

    public function karigarReport(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        $karigars = \App\Models\Supplier::when($branchId, function ($q) use ($branchId) {
            return $q->where('branch_id', $branchId);
        })
            ->where('supplier_type', 'Karigar')->get();
        $selectedKarigarId = $request->karigar_id;

        $query = \App\Models\ServiceJobItem::with('serviceJob.karigar');

        if ($selectedKarigarId) {
            $query->whereHas('serviceJob', function ($q) use ($selectedKarigarId) {
                $q->where('karigar_id', $selectedKarigarId);
            });
        }

        $items = $query->latest()->paginate(20);

        return view('service.reports.karigar_report', compact('karigars', 'items', 'selectedKarigarId'));
    }
}
