<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SalesController extends Controller
{
    /**
     * Display a listing of the sales.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Sale::class);

        $query = Sale::with(['user', 'client']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('date_from')) {
            $query->whereDate('sale_date', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('sale_date', '<=', $request->get('date_to'));
        }

        // Role-based filtering
        if (Auth::user()->hasRole('virtual_assistant')) {
            $query->where('user_id', Auth::id());
        }

        $sales = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json($sales);
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Sale::class);

        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'status' => 'required|in:pending,completed,cancelled',
            'payment_method' => 'required|in:cash,card,online',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        $sale = Sale::create($validated);

        return response()->json([
            'message' => 'Sale created successfully',
            'data' => $sale->load(['user', 'client'])
        ], 201);
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        Gate::authorize('view', $sale);

        return response()->json($sale->load(['user', 'client']));
    }

    /**
     * Update the specified sale in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        Gate::authorize('update', $sale);

        $validated = $request->validate([
            'client_id' => 'sometimes|required|exists:users,id',
            'product_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'sometimes|required|numeric|min:0',
            'sale_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:pending,completed,cancelled',
            'payment_method' => 'sometimes|required|in:cash,card,online',
            'notes' => 'nullable|string',
        ]);

        $sale->update($validated);

        return response()->json([
            'message' => 'Sale updated successfully',
            'data' => $sale->load(['user', 'client'])
        ]);
    }

    /**
     * Remove the specified sale from storage.
     */
    public function destroy(Sale $sale)
    {
        Gate::authorize('delete', $sale);

        $sale->delete();

        return response()->json([
            'message' => 'Sale deleted successfully'
        ], 204);
    }

    /**
     * Get sales statistics
     */
    public function statistics(Request $request)
    {
        Gate::authorize('viewAny', Sale::class);

        $query = Sale::query();

        // Role-based filtering
        if (Auth::user()->hasRole('virtual_assistant')) {
            $query->where('user_id', Auth::id());
        }

        $totalSales = $query->sum('amount');
        $totalCount = $query->count();
        $averageSale = $totalCount > 0 ? $totalSales / $totalCount : 0;

        $monthlySales = $query->selectRaw('SUM(amount) as total, MONTH(sale_date) as month')
                             ->whereYear('sale_date', date('Y'))
                             ->groupBy('month')
                             ->pluck('total', 'month');

        return response()->json([
            'total_sales' => $totalSales,
            'total_count' => $totalCount,
            'average_sale' => $averageSale,
            'monthly_sales' => $monthlySales
        ]);
    }
}