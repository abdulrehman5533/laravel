<?php

namespace App\Http\Controllers\Girvi;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Girvi;
use Illuminate\Http\Request;

class GirviLedgerController extends Controller
{
    public function customerLedger(Customer $customer)
    {
        $girvis = Girvi::where('customer_id', $customer->id)
            ->with(['payments', 'interestPostings', 'branch'])
            ->orderBy('girvi_date', 'desc')
            ->get();

        $transactions = collect();

        foreach ($girvis as $girvi) {
            // Opening entry
            $transactions->push([
                'date' => $girvi->girvi_date,
                'type' => 'Loan Disbursed',
                'girvi_number' => $girvi->girvi_number,
                'debit' => $girvi->loan_amount,
                'credit' => 0,
                'balance' => 0,
                'narration' => "Loan disbursed - {$girvi->girvi_number}",
            ]);

            // Interest postings
            foreach ($girvi->interestPostings as $posting) {
                $transactions->push([
                    'date' => $posting->posting_date,
                    'type' => 'Interest Accrued',
                    'girvi_number' => $girvi->girvi_number,
                    'debit' => $posting->interest_amount,
                    'credit' => 0,
                    'balance' => 0,
                    'narration' => "Interest for period {$posting->period_start->format('d/m/Y')} to {$posting->period_end->format('d/m/Y')}",
                ]);
            }

            // Payments
            foreach ($girvi->payments as $payment) {
                $transactions->push([
                    'date' => $payment->payment_date,
                    'type' => 'Payment Received',
                    'girvi_number' => $girvi->girvi_number,
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'balance' => 0,
                    'narration' => "Payment via {$payment->payment_method}" . ($payment->reference_number ? " - Ref: {$payment->reference_number}" : ''),
                ]);
            }
        }

        // Sort by date and calculate running balance
        $transactions = $transactions->sortBy('date')->values();
        $balance = 0;
        $transactions = $transactions->map(function ($txn) use (&$balance) {
            $balance += ($txn['debit'] - $txn['credit']);
            $txn['balance'] = $balance;
            return $txn;
        });

        return view('girvi.ledger.customer', compact('customer', 'transactions', 'girvis'));
    }

    public function loanLedger(Girvi $girvi)
    {
        $girvi->load(['customer', 'branch', 'payments', 'interestPostings', 'partialReleases', 'topups']);

        $transactions = collect();

        // Opening
        $transactions->push([
            'date' => $girvi->girvi_date,
            'type' => 'Loan Disbursed',
            'debit' => $girvi->loan_amount,
            'credit' => 0,
            'balance' => 0,
            'narration' => 'Principal amount disbursed',
        ]);

        // Interest postings
        foreach ($girvi->interestPostings as $posting) {
            $transactions->push([
                'date' => $posting->posting_date,
                'type' => 'Interest Accrued',
                'debit' => $posting->interest_amount,
                'credit' => 0,
                'balance' => 0,
                'narration' => "Interest: {$posting->period_start->format('d/m/Y')} to {$posting->period_end->format('d/m/Y')}",
            ]);
        }

        // Payments
        foreach ($girvi->payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date,
                'type' => 'Payment',
                'debit' => 0,
                'credit' => $payment->amount,
                'balance' => 0,
                'narration' => "Principal: Rs.{$payment->principal_component}, Interest: Rs.{$payment->interest_component} via {$payment->payment_method}",
            ]);
        }

        // Top-ups
        foreach ($girvi->topups as $topup) {
            $transactions->push([
                'date' => $topup->topup_date,
                'type' => 'Top-up',
                'debit' => $topup->topup_amount,
                'credit' => 0,
                'balance' => 0,
                'narration' => "Additional loan amount",
            ]);
        }

        // Partial releases
        foreach ($girvi->partialReleases as $release) {
            $transactions->push([
                'date' => $release->release_date,
                'type' => 'Partial Release',
                'debit' => 0,
                'credit' => $release->amount_paid,
                'balance' => 0,
                'narration' => "Partial release payment",
            ]);
        }

        // Sort and calculate balance
        $transactions = $transactions->sortBy('date')->values();
        $balance = 0;
        $transactions = $transactions->map(function ($txn) use (&$balance) {
            $balance += ($txn['debit'] - $txn['credit']);
            $txn['balance'] = $balance;
            return $txn;
        });

        return view('girvi.ledger.loan', compact('girvi', 'transactions'));
    }
}
