<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientMedia;
use App\Services\FileStorageService;
use App\Services\ImageService;
use App\Traits\AdministrativeAlertsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ClientController extends Controller
{
    use AdministrativeAlertsTrait;
    protected $fileStorageService;
    protected $imageService;

    public function __construct(FileStorageService $fileStorageService, ImageService $imageService)
    {
        $this->fileStorageService = $fileStorageService;
        $this->imageService = $imageService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Client::class);

        $query = Client::query();

        // Apply filters using the new filter scope
        $filters = $request->only(['search', 'category', 'company']);
        $query->filter($filters);

        $clients = $query->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $filters
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Client::class);

        return Inertia::render('Clients/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Client::class);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'company' => 'required|string|max:255',
            'category' => 'nullable|in:Consignment Partner,Direct Buyer,Wholesale Client,Retail Customer,Corporate Account,Auction House,Other',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        $client = Client::create($validated);

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $this->processClientImage($request->file('image'), $client);
        }

        return redirect()->route('clients.web.index')
            ->with('success', 'Client created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        Gate::authorize('view', $client);

        return Inertia::render('Clients/Show', [
            'client' => $client->load(['sales', 'contentPosts'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        Gate::authorize('update', $client);

        return Inertia::render('Clients/Edit', [
            'client' => $client
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        Gate::authorize('update', $client);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'company' => 'required|string|max:255',
            'category' => 'nullable|in:Consignment Partner,Direct Buyer,Wholesale Client,Retail Customer,Corporate Account,Auction House,Other',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        $client->update($validated);

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $this->processClientImage($request->file('image'), $client);
        }

        return redirect()->route('clients.web.index')
            ->with('success', 'Client updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        Gate::authorize('delete', $client);

        // Store client info before deletion for alert
        $clientInfo = [
            'id' => $client->id,
            'name' => $client->full_name,
            'email' => $client->email,
            'company' => $client->company,
            'category' => $client->category,
            'created_at' => $client->created_at,
        ];

        // Check if client has significant data
        $salesCount = $client->sales()->count();
        $contentCount = $client->contentPosts()->count();
        $isSignificant = $salesCount > 0 || $contentCount > 0;

        $client->delete();

        // Trigger client deletion alert
        $this->triggerClientDeletedAlert(
            (object) $clientInfo,
            'Client deleted by ' . auth()->user()->name,
            [
                'sales_count' => $salesCount,
                'content_count' => $contentCount,
                'was_significant' => $isSignificant,
                'category' => $client->category,
                'company' => $client->company,
            ]
        );

        return redirect()->route('clients.web.index')
            ->with('success', 'Client deleted successfully');
    }

    /**
     * Search clients for autocomplete functionality.
     */
    public function search(Request $request)
    {
        Gate::authorize('viewAny', Client::class);

        $request->validate([
            'search' => 'required|string|min:2'
        ]);

        $clients = Client::search($request->search)
            ->select('id', 'first_name', 'last_name', 'email', 'company', 'category')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(10)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->full_name,
                    'email' => $client->email,
                    'company' => $client->company,
                    'category' => $client->category
                ];
            });

        return response()->json($clients);
    }
    /**
     * Process client image upload
     */
    protected function processClientImage($imageFile, Client $client)
    {
        try {
            // Delete existing client media
            $client->media()->delete();

            // Store the new image
            $result = $this->fileStorageService->storeMediaFile($imageFile, 'clients');
            
            // Create client media record
            ClientMedia::create([
                'client_id' => $client->id,
                'user_id' => Auth::id(),
                'file_name' => $result['filename'],
                'file_path' => $result['path'],
                'mime_type' => $result['mime_type'],
                'file_size' => $result['file_size'],
                'original_name' => $result['original_name'],
                'order' => 0,
                'is_primary' => true,
            ]);

            Log::info('Client image uploaded successfully', [
                'client_id' => $client->id,
                'file_path' => $result['path']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process client image', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
