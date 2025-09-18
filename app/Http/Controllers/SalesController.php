<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

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
        if (Auth::user()->role === 'va') {
            $query->where('user_id', Auth::id());
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Format the sales data to match frontend expectations
        $sales->getCollection()->transform(function ($sale) {
            return [
                'id' => $sale->id,
                'product_name' => $sale->product_name,
                'client' => $sale->client ? [
                    'id' => $sale->client->id,
                    'name' => $sale->client->first_name . ' ' . $sale->client->last_name,
                    'email' => $sale->client->email,
                ] : null,
                'amount' => $sale->amount,
                'sale_date' => $sale->sale_date,
                'status' => $sale->status,
                'payment_method' => $sale->payment_method,
                'description' => $sale->description,
                'type' => $sale->type,
                'created_at' => $sale->created_at,
                'updated_at' => $sale->updated_at,
            ];
        });

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'min_amount', 'max_amount'])
        ]);
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create()
    {
        Gate::authorize('create', Sale::class);

        $clients = \App\Models\Client::select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->first_name . ' ' . $client->last_name,
                    'email' => $client->email
                ];
            });

        return Inertia::render('Sales/Create', [
            'clients' => $clients
        ]);
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Sale::class);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'status' => 'required|in:pending,completed,cancelled',
            'payment_method' => 'required|in:cash,card,online',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['type'] = 'Cards'; // Default type, adjust as needed

        // Map form fields to database columns
        $saleData = [
            'user_id' => $validated['user_id'],
            'client_id' => $validated['client_id'],
            'type' => $validated['type'],
            'product_name' => $validated['product_name'],
            'amount' => $validated['amount'],
            'sale_date' => $validated['sale_date'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'payment_method' => $validated['payment_method'],
        ];

        $sale = Sale::create($saleData);

        return redirect()->route('sales.index')
            ->with('success', 'Sale created successfully');
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        Gate::authorize('view', $sale);

        return Inertia::render('Sales/Show', [
            'sale' => $sale->load(['user', 'client'])
        ]);
    }

    /**
     * Show the form for editing the specified sale.
     */
    public function edit(Sale $sale)
    {
        Gate::authorize('update', $sale);

        return Inertia::render('Sales/Edit', [
            'sale' => $sale->load(['user', 'client'])
        ]);
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

        $oldStatus = $sale->status;
        $sale->update($validated);

        // Send notification if status changed to completed
        if ($oldStatus !== 'completed' && $sale->status === 'completed') {
            \App\Services\NotificationService::sendSaleCompletion(
                $sale->user,
                $sale->amount,
                $sale->client->name ?? 'Unknown Client',
                ['sale_id' => $sale->id]
            );
        }

        return redirect()->route('sales.index')
            ->with('success', 'Sale updated successfully');
    }

    /**
     * Remove the specified sale from storage.
     */
    public function destroy(Sale $sale)
    {
        Gate::authorize('delete', $sale);

        $sale->delete();

        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully');
    }

    /**
     * Search clients by name or email
     */
    public function searchClients(Request $request)
    {
        Gate::authorize('viewAny', Sale::class);

        $search = $request->get('search', '');

        $clients = Client::search($search)
            ->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(10)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->full_name,
                    'email' => $client->email
                ];
            });

        return response()->json($clients);
    }

    /**
     * Create a new client
     */
    public function createClient(Request $request)
    {
        Gate::authorize('create', Sale::class);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
        ]);

        $client = Client::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
        ]);

        return response()->json([
            'id' => $client->id,
            'name' => $client->full_name,
            'email' => $client->email
        ], 201);
    }

    /**
     * Get sales statistics
     */
    public function statistics(Request $request)
    {
        Gate::authorize('viewAny', Sale::class);

        $query = Sale::query();

        // Role-based filtering
        if (Auth::user()->role === 'va') {
            $query->where('user_id', Auth::id());
        }

        $totalSales = $query->sum('amount');
        $totalCount = $query->count();
        $averageSale = $totalCount > 0 ? $totalSales / $totalCount : 0;

        $monthlySales = $query->selectRaw('SUM(amount) as total, MONTH(sale_date) as month')
            ->whereYear('sale_date', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month');

        return Inertia::render('Sales/Statistics', [
            'statistics' => [
                'total_sales' => $totalSales,
                'total_count' => $totalCount,
                'average_sale' => $averageSale,
                'monthly_sales' => $monthlySales
            ]
        ]);
    }
}
