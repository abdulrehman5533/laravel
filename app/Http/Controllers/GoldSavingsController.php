<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\GoldRate;
use App\Models\GoldSavingsScheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoldSavingsController extends Controller
{
    public function index()
    {
        $schemes = GoldSavingsScheme::with('customer')->latest()->paginate(20);
        $stats = [
            'active'             => GoldSavingsScheme::where('status', 'active')->count(),
            'matured'            => GoldSavingsScheme::where('status', 'matured')->count(),
            'total_accumulated'  => GoldSavingsScheme::where('status', 'active')->sum('accumulated_amount'),
            'total_gold_weight'  => GoldSavingsScheme::where('status', 'active')->sum('accumulated_weight'),
        ];
        return view('gold-savings.index', compact('schemes', 'stats'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $branches  = Branch::where('is_active', true)->get();
        $goldRate  = GoldRate::latest('date')->first();
        return view('gold-savings.create', compact('customers', 'branches', 'goldRate'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'scheme_name'    => 'required|string|max:255',
            'monthly_amount' => 'required|numeric|min:1',
            'duration_months'=> 'required|integer|min:1|max:60',
            'start_date'     => 'required|date',
            'remarks'        => 'nullable|string',
        ]);

        $data['end_date']            = \Carbon\Carbon::parse($data['start_date'])->addMonths($data['duration_months']);
        $data['accumulated_amount']  = 0;
        $data['accumulated_weight']  = 0;
        $data['status']              = 'active';

        GoldSavingsScheme::create($data);
        return redirect()->route('gold-savings.index')->with('success', 'Gold Savings Scheme created.');
    }

    public function show(GoldSavingsScheme $scheme)
    {
        $scheme->load('customer', 'payments');
        $goldRate = GoldRate::latest('date')->first();
        $totalMonths   = $scheme->duration_months;
        $paidMonths    = $scheme->payments->count();
        $remainingMonths = max(0, $totalMonths - $paidMonths);
        return view('gold-savings.show', compact('scheme', 'goldRate', 'paidMonths', 'remainingMonths'));
    }

    public function recordPayment(Request $request, GoldSavingsScheme $scheme)
    {
        $data = $request->validate([
            'amount'     => 'required|numeric|min:1',
            'gold_rate'  => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'notes'      => 'nullable|string',
        ]);

        DB::transaction(function () use ($scheme, $data) {
            $goldWeight = $data['amount'] / $data['gold_rate'];

            $scheme->payments()->create([
                'amount'       => $data['amount'],
                'gold_rate'    => $data['gold_rate'],
                'gold_weight'  => $goldWeight,
                'payment_date' => $data['payment_date'],
                'notes'        => $data['notes'] ?? null,
            ]);

            $scheme->increment('accumulated_amount', $data['amount']);
            $scheme->increment('accumulated_weight', $goldWeight);

            // Auto-mature if all payments done
            $paidMonths = $scheme->payments()->count();
            if ($paidMonths >= $scheme->duration_months) {
                $scheme->update(['status' => 'matured']);
            }
        });

        return redirect()->back()->with('success', 'Payment recorded. Gold weight added to scheme.');
    }

    public function mature(GoldSavingsScheme $scheme)
    {
        $scheme->update(['status' => 'matured']);
        return redirect()->back()->with('success', 'Scheme marked as matured.');
    }
}
