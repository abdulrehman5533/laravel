<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CrmController extends Controller
{
    /**
     * Display CRM dashboard
     */
    public function dashboard(): View
    {
        $totalCustomers = \App\Models\Customer::count();

        return view('crm.dashboard', [
            'totalCustomers' => $totalCustomers,
        ]);
    }
}
