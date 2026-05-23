<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CrmInteraction;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CrmController extends Controller
{
    public function dashboard()
    {
        $totalCustomers   = Customer::count();
        $activeCustomers  = Customer::where('is_active', true)->count();
        $newThisMonth     = Customer::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Birthdays this week
        $birthdaysThisWeek = Customer::whereNotNull('date_of_birth')
            ->whereRaw('DATE_FORMAT(date_of_birth, "%m-%d") BETWEEN ? AND ?', [
                now()->format('m-d'),
                now()->addDays(7)->format('m-d'),
            ])->get();

        // Anniversaries this week
        $anniversariesThisWeek = Customer::whereNotNull('anniversary_date')
            ->whereRaw('DATE_FORMAT(anniversary_date, "%m-%d") BETWEEN ? AND ?', [
                now()->format('m-d'),
                now()->addDays(7)->format('m-d'),
            ])->get();

        // Follow-ups due today/overdue
        $followUpsDue = CrmInteraction::with('customer')
            ->where('follow_up_date', '<=', now())
            ->where('status', '!=', 'closed')
            ->orderBy('follow_up_date')
            ->take(10)
            ->get();

        // Top customers by purchase
        $topCustomers = Customer::orderByDesc('total_purchases')
            ->take(5)->get();

        // Recent interactions
        $recentInteractions = CrmInteraction::with('customer')
            ->latest('interaction_date')
            ->take(8)->get();

        // Membership breakdown
        $membershipBreakdown = Customer::select('membership_level', DB::raw('count(*) as count'))
            ->groupBy('membership_level')
            ->get();

        // Outstanding balances
        $outstandingCount  = Customer::where('current_balance', '>', 0)->count();
        $outstandingAmount = Customer::sum('current_balance');

        return view('crm.dashboard', compact(
            'totalCustomers', 'activeCustomers', 'newThisMonth',
            'birthdaysThisWeek', 'anniversariesThisWeek',
            'followUpsDue', 'topCustomers', 'recentInteractions',
            'membershipBreakdown', 'outstandingCount', 'outstandingAmount'
        ));
    }
}
