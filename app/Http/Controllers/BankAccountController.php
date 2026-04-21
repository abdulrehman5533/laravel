<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\Branch;
use App\Models\ChequeManagement;
use App\Models\DigitalPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function index(Request $request): View
    {
        $query = BankAccount::query();

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $accounts = $query->with('branch')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        return view('accounts.bank.index', compact('accounts', 'branches'));
    }

    public function create(): View
    {
        $branches = Branch::where('is_active', true)->get();

        return view('accounts.bank.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'account_name' => 'required|string|max:100',
            'account_number' => 'required|string|unique:bank_accounts',
            'bank_name' => 'required|string|max:100',
            'ifsc_code' => 'nullable|string',
            'account_type' => 'required|in:Savings,Current,Business',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $validated['current_balance'] = $validated['opening_balance'];
        $validated['is_active'] = true;

        BankAccount::create($validated);

        return redirect()->route('accounts.bank-payments.index')
            ->with('success', 'Bank account created successfully');
    }

    public function show(BankAccount $account): View
    {
        $account->load(['branch', 'transactions', 'cheques', 'statements']);

        return view('accounts.bank.show', compact('account'));
    }

    public function edit(BankAccount $account): View
    {
        $branches = Branch::where('is_active', true)->get();

        return view('accounts.bank.edit', compact('account', 'branches'));
    }

    public function update(Request $request, BankAccount $account): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'account_name' => 'required|string|max:100',
            'account_number' => 'required|string|unique:bank_accounts,account_number,'.$account->id,
            'bank_name' => 'required|string|max:100',
            'ifsc_code' => 'nullable|string',
            'account_type' => 'required|in:Savings,Current,Business',
        ]);

        $account->update($validated);

        return redirect()->route('accounts.bank-payments.index')
            ->with('success', 'Bank account updated successfully');
    }

    public function destroy(BankAccount $account): RedirectResponse
    {
        $account->delete();

        return redirect()->route('accounts.bank.index')
            ->with('success', 'Bank account deleted successfully');
    }

    public function transactions(BankAccount $account, Request $request): View
    {
        $query = $account->transactions();

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('transaction_date', [$request->date_from, $request->date_to]);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(30);

        return view('accounts.bank.transactions', compact('account', 'transactions'));
    }

    public function recordTransaction(Request $request, BankAccount $account): RedirectResponse
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:deposit,withdrawal,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:200',
        ]);

        $newBalance = match ($validated['transaction_type']) {
            'deposit', 'transfer' => $account->current_balance + $validated['amount'],
            'withdrawal' => $account->current_balance - $validated['amount'],
        };

        BankTransaction::create([
            'bank_account_id' => $account->id,
            'transaction_date' => $validated['transaction_date'],
            'transaction_type' => $validated['transaction_type'],
            'amount' => $validated['amount'],
            'balance_after' => $newBalance,
            'description' => $validated['description'],
        ]);

        $account->update(['current_balance' => $newBalance]);

        return back()->with('success', 'Transaction recorded successfully');
    }

    public function cheques(BankAccount $account): View
    {
        $cheques = $account->cheques()->paginate(20);

        return view('accounts.bank.cheques', compact('account', 'cheques'));
    }

    public function recordCheque(Request $request, BankAccount $account): RedirectResponse
    {
        $validated = $request->validate([
            'cheque_number' => 'required|string|unique:cheque_management',
            'cheque_type' => 'required|in:issued,received',
            'amount' => 'required|numeric|min:0.01',
            'cheque_date' => 'required|date',
            'payee_name' => 'nullable|string',
            'payer_name' => 'nullable|string',
        ]);

        $validated['bank_account_id'] = $account->id;
        $validated['status'] = 'issued';

        ChequeManagement::create($validated);

        return back()->with('success', 'Cheque recorded successfully');
    }

    public function clearCheque(ChequeManagement $cheque): RedirectResponse
    {
        $cheque->update([
            'status' => 'cleared',
            'clearing_date' => now()->toDateString(),
        ]);

        return back()->with('success', 'Cheque marked as cleared');
    }

    public function bounceCheque(Request $request, ChequeManagement $cheque): RedirectResponse
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:300',
        ]);

        $cheque->update([
            'status' => 'bounced',
            'remarks' => $validated['remarks'],
        ]);

        return back()->with('error', 'Cheque marked as bounced');
    }

    public function reconcile(Request $request, BankAccount $account): RedirectResponse
    {
        $validated = $request->validate([
            'statement_date' => 'required|date',
            'bank_balance' => 'required|numeric',
            'statement_file' => 'nullable|file|mimes:pdf,xlsx,csv',
        ]);

        $statement = BankStatement::create([
            'bank_account_id' => $account->id,
            'statement_date' => $validated['statement_date'],
            'opening_balance' => $account->current_balance,
            'closing_balance' => $validated['bank_balance'],
            'status' => 'pending',
        ]);

        if ($request->hasFile('statement_file')) {
            $path = $request->file('statement_file')->store('statements', 'public');
            $statement->update(['file_path' => $path]);
        }

        return back()->with('success', 'Bank reconciliation started');
    }

    public function digitalPayments(): View
    {
        $payments = DigitalPayment::orderBy('transaction_date', 'desc')->paginate(20);

        return view('accounts.bank.digital-payments', compact('payments'));
    }

    public function recordDigitalPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_gateway' => 'required|in:JazzCash,Easypaisa,Bank QR,POS',
            'amount' => 'required|numeric|min:0.01',
            'customer_identifier' => 'nullable|string',
            'reference_type' => 'nullable|string',
        ]);

        $validated['transaction_id'] = 'TXN-'.date('YmdHis');
        $validated['transaction_date'] = now()->toDateString();
        $validated['status'] = 'success';

        DigitalPayment::create($validated);

        return back()->with('success', 'Digital payment recorded successfully');
    }
}
