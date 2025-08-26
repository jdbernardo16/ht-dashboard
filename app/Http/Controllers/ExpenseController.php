<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the expenses.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Expense::class);

        $query = Expense::with(['user']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('date_from')) {
            $query->whereDate('expense_date', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('expense_date', '<=', $request->get('date_to'));
        }

        // Role-based filtering
        if (Auth::user()->role === 'va') {
            $query->where('user_id', Auth::id());
        }

        $expenses = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Format the expenses data to match frontend expectations
        $expenses->getCollection()->transform(function ($expense) {
            return [
                'id' => $expense->id,
                'description' => $expense->description,
                'category' => $expense->category,
                'amount' => $expense->amount,
                'expense_date' => $expense->expense_date,
                'status' => $expense->status,
                'payment_method' => $expense->payment_method,
                'merchant' => $expense->merchant,
                'receipt_number' => $expense->receipt_number,
                'tax_amount' => $expense->tax_amount,
                'notes' => $expense->notes,
                'user' => $expense->user ? [
                    'id' => $expense->user->id,
                    'name' => $expense->user->first_name . ' ' . $expense->user->last_name,
                    'email' => $expense->user->email,
                ] : null,
                'created_at' => $expense->created_at,
                'updated_at' => $expense->updated_at,
            ];
        });

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'min_amount', 'max_amount'])
        ]);
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create()
    {
        Gate::authorize('create', Expense::class);

        return Inertia::render('Expenses/Create');
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Expense::class);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string|max:255',
            'status' => 'required|in:pending,paid,cancelled',
            'payment_method' => 'required|in:cash,card,online,bank_transfer',
            'merchant' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        $expense = Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense created successfully');
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense)
    {
        Gate::authorize('view', $expense);

        return Inertia::render('Expenses/Show', [
            'expense' => $expense->load(['user'])
        ]);
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense)
    {
        Gate::authorize('update', $expense);

        return Inertia::render('Expenses/Edit', [
            'expense' => $expense->load(['user'])
        ]);
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        Gate::authorize('update', $expense);

        $validated = $request->validate([
            'description' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'expense_date' => 'sometimes|required|date',
            'category' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:pending,paid,cancelled',
            'payment_method' => 'sometimes|required|in:cash,card,online,bank_transfer',
            'merchant' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $expense->status;
        $expense->update($validated);

        // Send notification if status changed to paid (approved)
        if ($oldStatus !== 'paid' && $expense->status === 'paid') {
            \App\Services\NotificationService::sendExpenseApproval(
                $expense->user,
                $expense->amount,
                $expense->category,
                ['expense_id' => $expense->id]
            );
        }

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully');
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense)
    {
        Gate::authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully');
    }

    /**
     * Get expenses statistics
     */
    public function statistics(Request $request)
    {
        Gate::authorize('viewAny', Expense::class);

        $query = Expense::query();

        // Role-based filtering
        if (Auth::user()->role === 'va') {
            $query->where('user_id', Auth::id());
        }

        $totalExpenses = $query->sum('amount');
        $totalCount = $query->count();
        $averageExpense = $totalCount > 0 ? $totalExpenses / $totalCount : 0;

        $monthlyExpenses = $query->selectRaw('SUM(amount) as total, MONTH(expense_date) as month')
            ->whereYear('expense_date', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month');

        return Inertia::render('Expenses/Statistics', [
            'statistics' => [
                'total_expenses' => $totalExpenses,
                'total_count' => $totalCount,
                'average_expense' => $averageExpense,
                'monthly_expenses' => $monthlyExpenses
            ]
        ]);
    }
}
