<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the expenses.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Expense::class);

        $query = Expense::with(['user', 'category']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('merchant', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
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

        if ($request->has('min_amount')) {
            $query->where('amount', '>=', $request->get('min_amount'));
        }

        if ($request->has('max_amount')) {
            $query->where('amount', '<=', $request->get('max_amount'));
        }

        // Role-based filtering
        if (Auth::user()->hasRole('virtual_assistant')) {
            $query->where('user_id', Auth::id());
        }

        $expenses = $query->orderBy('expense_date', 'desc')
                         ->paginate($request->get('per_page', 15));

        return response()->json($expenses);
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Expense::class);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'merchant' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,online,bank_transfer',
            'receipt_number' => 'nullable|string|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly,yearly|required_if:is_recurring,true',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $validated['user_id'] = Auth::id();

        $expense = Expense::create($validated);

        return response()->json([
            'message' => 'Expense created successfully',
            'data' => $expense->load(['user', 'category'])
        ], 201);
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense)
    {
        Gate::authorize('view', $expense);

        return response()->json($expense->load(['user', 'category']));
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        Gate::authorize('update', $expense);

        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'description' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'expense_date' => 'sometimes|required|date',
            'merchant' => 'nullable|string|max:255',
            'payment_method' => 'sometimes|required|in:cash,card,online,bank_transfer',
            'receipt_number' => 'nullable|string|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly,yearly|required_if:is_recurring,true',
            'status' => 'sometimes|required|in:pending,approved,rejected',
        ]);

        $expense->update($validated);

        return response()->json([
            'message' => 'Expense updated successfully',
            'data' => $expense->load(['user', 'category'])
        ]);
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense)
    {
        Gate::authorize('delete', $expense);

        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully'
        ], 204);
    }

    /**
     * Get expense statistics
     */
    public function statistics(Request $request)
    {
        Gate::authorize('viewAny', Expense::class);

        $query = Expense::query();

        // Role-based filtering
        if (Auth::user()->hasRole('virtual_assistant')) {
            $query->where('user_id', Auth::id());
        }

        $totalExpenses = $query->sum('amount');
        $totalCount = $query->count();
        $averageExpense = $totalCount > 0 ? $totalExpenses / $totalCount : 0;

        $monthlyExpenses = $query->selectRaw('SUM(amount) as total, MONTH(expense_date) as month')
                                ->whereYear('expense_date', date('Y'))
                                ->groupBy('month')
                                ->pluck('total', 'month');

        $expensesByCategory = $query->selectRaw('categories.name as category, SUM(expenses.amount) as total')
                                   ->join('categories', 'expenses.category_id', '=', 'categories.id')
                                   ->groupBy('categories.id', 'categories.name')
                                   ->pluck('total', 'category');

        return response()->json([
            'total_expenses' => $totalExpenses,
            'total_count' => $totalCount,
            'average_expense' => $averageExpense,
            'monthly_expenses' => $monthlyExpenses,
            'expenses_by_category' => $expensesByCategory
        ]);
    }
}