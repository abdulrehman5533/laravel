<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReconcileRequest;
use App\Http\Requests\StoreChequeRequest;
use App\Http\Requests\StoreDepositRequest;
use App\Http\Requests\StoreTransferRequest;
use App\Http\Requests\StoreWithdrawalRequest;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\ChartOfAccount;
use App\Models\ChequeManagement;
use Illuminate\Http\Request;

class BankPaymentsController extends Controller
{
    /**
     * Display bank accounts dashboard
     */
    public function index()
    {
        $bankAccounts = BankAccount::with('transactions')->get();

        $totalBalance = $bankAccounts->sum('current_balance');
        $totalDeposits = BankTransaction::where('transaction_type', 'deposit')->sum('amount');
        $totalWithdrawals = BankTransaction::where('transaction_type', 'withdrawal')->sum('amount');

        $recentTransactions = BankTransaction::latest()->take(10)->get();

        // Issued cheques
        $issuedCheques = ChequeManagement::latest()->take(10)->get();

        // Reconciliation data
        $totalBookBalance = $bankAccounts->sum('current_balance');
        $totalBankBalance = BankTransaction::sum('balance_after');

        return view('accounts.bank-payments.index', [
            'bankAccounts' => $bankAccounts,
            'totalBalance' => $totalBalance,
            'totalDeposits' => $totalDeposits,
            'totalWithdrawals' => $totalWithdrawals,
            'recentTransactions' => $recentTransactions,
            'issuedCheques' => $issuedCheques,
            'totalBookBalance' => $totalBookBalance,
            'totalBankBalance' => $totalBankBalance,
        ]);
    }

    /**
     * Create new bank deposit
     */
    public function createDeposit()
    {
        $bankAccounts = BankAccount::where('is_active', true)->get();

        return view('accounts.bank-payments.deposit', ['bankAccounts' => $bankAccounts]);
    }

    /**
     * Store bank deposit
     */
    public function storeDeposit(StoreDepositRequest $request)
    {
        $validated = $request->validated();

        // Update account balance
        $bankAccount = BankAccount::find($validated['bank_account_id']);
        $bankAccount->increment('current_balance', $validated['amount']);

        // Create transaction
        $transaction = BankTransaction::create([
            'bank_account_id' => $validated['bank_account_id'],
            'transaction_type' => 'deposit',
            'amount' => $validated['amount'],
            'balance_after' => $bankAccount->current_balance,
            'description' => $validated['notes'] ?? null,
            'transaction_date' => now(),
            'status' => 'completed',
        ]);

        // Create journal entries for double-entry
        $this->createJournalEntry(
            'deposit',
            $bankAccount->chart_account_id ?? null,
            $validated['amount'],
            'Bank Deposit - '.($validated['reference_number'] ?? 'N/A')
        );

        return redirect()->route('accounts.bank-payments.index')
            ->with('success', 'Bank deposit recorded successfully');
    }

    /**
     * Create bank withdrawal
     */
    public function createWithdrawal()
    {
        $bankAccounts = BankAccount::where('is_active', true)->get();

        return view('accounts.bank-payments.withdrawal', ['bankAccounts' => $bankAccounts]);
    }

    /**
     * Store bank withdrawal
     */
    public function storeWithdrawal(StoreWithdrawalRequest $request)
    {
        $validated = $request->validated();

        $bankAccount = BankAccount::find($validated['bank_account_id']);

        // Check sufficient balance
        if ($bankAccount->current_balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient bank balance']);
        }

        // Update account balance
        $bankAccount->decrement('current_balance', $validated['amount']);

        // Create transaction
        $transaction = BankTransaction::create([
            'bank_account_id' => $validated['bank_account_id'],
            'transaction_type' => 'withdrawal',
            'amount' => $validated['amount'],
            'balance_after' => $bankAccount->current_balance,
            'description' => $validated['notes'] ?? null,
            'transaction_date' => now(),
            'status' => 'completed',
        ]);

        // Create journal entries
        $this->createJournalEntry(
            'withdrawal',
            $bankAccount->chart_account_id ?? null,
            $validated['amount'],
            'Bank Withdrawal - '.($validated['reference_number'] ?? 'N/A')
        );

        return redirect()->route('accounts.bank-payments.index')
            ->with('success', 'Bank withdrawal recorded successfully');
    }

    /**
     * Bank transfers between accounts
     */
    public function createTransfer()
    {
        $bankAccounts = BankAccount::where('is_active', true)->get();

        return view('accounts.bank-payments.transfer', ['bankAccounts' => $bankAccounts]);
    }

    /**
     * Store bank transfer
     */
    public function storeTransfer(StoreTransferRequest $request)
    {
        $validated = $request->validated();

        $fromAccount = BankAccount::find($validated['from_account_id']);
        $toAccount = BankAccount::find($validated['to_account_id']);

        if ($fromAccount->current_balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient balance in source account']);
        }

        // Update balances
        $fromAccount->decrement('current_balance', $validated['amount']);
        $toAccount->increment('current_balance', $validated['amount']);

        // Create transactions
        BankTransaction::create([
            'bank_account_id' => $validated['from_account_id'],
            'transaction_type' => 'withdrawal',
            'amount' => $validated['amount'],
            'balance_after' => $fromAccount->current_balance,
            'description' => "Transfer to {$toAccount->bank_name} - ".($validated['notes'] ?? ''),
            'transaction_date' => now(),
            'status' => 'completed',
        ]);

        BankTransaction::create([
            'bank_account_id' => $validated['to_account_id'],
            'transaction_type' => 'deposit',
            'amount' => $validated['amount'],
            'balance_after' => $toAccount->current_balance,
            'description' => "Transfer from {$fromAccount->bank_name} - ".($validated['notes'] ?? ''),
            'transaction_date' => now(),
            'status' => 'completed',
        ]);

        return redirect()->route('accounts.bank-payments.index')
            ->with('success', 'Bank transfer completed successfully');
    }

    /**
     * Cheque management
     */
    public function manageCheques(Request $request)
    {
        $status = $request->get('status');
        $query = ChequeManagement::with('bankAccount');

        if ($status) {
            $query->where('status', $status);
        }

        $cheques = $query->latest()->paginate(20);

        return view('accounts.bank-payments.cheques', ['cheques' => $cheques]);
    }

    /**
     * Issue new cheque
     */
    public function issueCheque()
    {
        $bankAccounts = BankAccount::where('is_active', true)->get();

        return view('accounts.bank-payments.cheque-create', ['bankAccounts' => $bankAccounts]);
    }

    /**
     * Store issued cheque
     */
    public function storeIssuedCheque(StoreChequeRequest $request)
    {
        $validated = $request->validated();

        $cheque = ChequeManagement::create([
            'bank_account_id' => $validated['bank_account_id'],
            'cheque_number' => $validated['cheque_number'],
            'cheque_type' => 'issued',
            'payee_name' => $validated['payee_name'],
            'amount' => $validated['amount'],
            'cheque_date' => $validated['cheque_date'],
            'status' => 'issued',
            'remarks' => $validated['memo'] ?? null,
        ]);

        return redirect()->route('accounts.bank-payments.cheques.index')
            ->with('success', 'Cheque issued successfully');
    }

    /**
     * Bank reconciliation
     */
    public function reconcile(ReconcileRequest $request)
    {
        $validated = $request->validated();

        $accountId = $validated['bank_account_id'];
        $bankAccount = BankAccount::findOrFail($accountId);

        // Calculate reconciliation
        $bookBalance = $bankAccount->current_balance;
        $statementBalance = $validated['statement_balance'];

        $difference = $bookBalance - $statementBalance;
        $outstandingDeposits = $validated['outstanding_deposits'] ?? 0;
        $outstandingCheques = $validated['outstanding_cheques'] ?? 0;
        $bankCharges = $validated['bank_charges'] ?? 0;
        $interestEarned = $validated['interest_earned'] ?? 0;

        $reconciliation = [
            'book_balance' => $bookBalance,
            'statement_balance' => $statementBalance,
            'outstanding_deposits' => $outstandingDeposits,
            'outstanding_cheques' => $outstandingCheques,
            'bank_charges' => $bankCharges,
            'interest_earned' => $interestEarned,
            'difference' => $difference,
        ];

        return view('accounts.bank-payments.reconcile', [
            'bankAccount' => $bankAccount,
            'reconciliation' => $reconciliation,
        ]);
    }

    /**
     * Helper function to create journal entries
     */
    private function createJournalEntry($type, $accountId, $amount, $description)
    {
        $journalEntry = \App\Models\JournalEntry::create([
            'entry_date' => now(),
            'description' => $description,
            'reference_number' => 'BANK-'.time(),
            'posted_by' => auth()->id(),
            'status' => 'posted',
        ]);

        // Create debit/credit entries based on type
        if ($type === 'deposit') {
            \App\Models\GeneralLedger::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $accountId,
                'type' => 'DEBIT',
                'amount' => $amount,
                'created_at' => now(),
            ]);

            // Credit to revenue or liability
            $incomeAccount = ChartOfAccount::byType('REVENUE')->first();
            if ($incomeAccount) {
                \App\Models\GeneralLedger::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $incomeAccount->id,
                    'type' => 'CREDIT',
                    'amount' => $amount,
                    'created_at' => now(),
                ]);
            }
        }
    }
}
