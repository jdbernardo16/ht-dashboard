<?php

namespace App\Http\Controllers;

use App\Models\ContentPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ContentPostController extends Controller
{
    /**
     * Display a listing of the content posts.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ContentPost::class);

        $query = ContentPost::with(['user', 'client']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('content_url', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('platform')) {
            $query->where('platform', $request->get('platform'));
        }

        if ($request->has('content_type')) {
            $query->where('content_type', $request->get('content_type'));
        }

        if ($request->has('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->get('date_to'));
        }

        // Role-based filtering
        if (Auth::user()->role === 'va') {
            $query->where('user_id', Auth::id());
        }

        $contentPosts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Format the content posts data to match frontend expectations
        $contentPosts->getCollection()->transform(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'description' => $post->description,
                'platform' => $post->platform,
                'content_type' => $post->content_type,
                'content_url' => $post->content_url,
                'post_count' => $post->post_count,
                'scheduled_date' => $post->scheduled_date,
                'published_date' => $post->published_date,
                'status' => $post->status,
                'content_category' => $post->content_category,
                'tags' => $post->tags,
                'notes' => $post->notes,
                'engagement_metrics' => $post->engagement_metrics,
                'engagement_rate' => $post->engagement_rate,
                'total_engagement' => $post->total_engagement,
                'client' => $post->client ? [
                    'id' => $post->client->id,
                    'name' => $post->client->first_name . ' ' . $post->client->last_name,
                    'email' => $post->client->email,
                ] : null,
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->first_name . ' ' . $post->user->last_name,
                    'email' => $post->user->email,
                ],
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ];
        });

        return Inertia::render('Content/Index', [
            'contentPosts' => $contentPosts,
            'filters' => $request->only(['search', 'status', 'platform', 'content_type', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Show the form for creating a new content post.
     */
    public function create()
    {
        Gate::authorize('create', ContentPost::class);

        $clients = User::where('role', 'client')
            ->select('id', 'first_name', 'last_name', 'email')
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

        return Inertia::render('Content/Create', [
            'clients' => $clients
        ]);
    }

    /**
     * Store a newly created content post in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', ContentPost::class);

        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
            'platform' => 'required|string|max:255',
            'content_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_url' => 'nullable|string|max:255',
            'post_count' => 'nullable|integer|min:1',
            'scheduled_date' => 'nullable|date',
            'published_date' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,published,archived',
            'content_category' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'engagement_metrics' => 'nullable|array',
        ]);

        $validated['user_id'] = Auth::id();

        $contentPost = ContentPost::create($validated);

        return redirect()->route('content.index')
            ->with('success', 'Content post created successfully');
    }

    /**
     * Display the specified content post.
     */
    public function show(ContentPost $contentPost)
    {
        Gate::authorize('view', $contentPost);

        $contentPost->load(['user', 'client']);

        return Inertia::render('Content/Show', [
            'contentPost' => [
                'id' => $contentPost->id,
                'title' => $contentPost->title,
                'description' => $contentPost->description,
                'platform' => $contentPost->platform,
                'content_type' => $contentPost->content_type,
                'content_url' => $contentPost->content_url,
                'post_count' => $contentPost->post_count,
                'scheduled_date' => $contentPost->scheduled_date,
                'published_date' => $contentPost->published_date,
                'status' => $contentPost->status,
                'content_category' => $contentPost->content_category,
                'tags' => $contentPost->tags,
                'notes' => $contentPost->notes,
                'engagement_metrics' => $contentPost->engagement_metrics,
                'engagement_rate' => $contentPost->engagement_rate,
                'total_engagement' => $contentPost->total_engagement,
                'client' => $contentPost->client ? [
                    'id' => $contentPost->client->id,
                    'name' => $contentPost->client->first_name . ' ' . $contentPost->client->last_name,
                    'email' => $contentPost->client->email,
                ] : null,
                'user' => [
                    'id' => $contentPost->user->id,
                    'name' => $contentPost->user->first_name . ' ' . $contentPost->user->last_name,
                    'email' => $contentPost->user->email,
                ],
                'created_at' => $contentPost->created_at,
                'updated_at' => $contentPost->updated_at,
            ]
        ]);
    }

    /**
     * Show the form for editing the specified content post.
     */
    public function edit(ContentPost $contentPost)
    {
        Gate::authorize('update', $contentPost);

        $clients = User::where('role', 'client')
            ->select('id', 'first_name', 'last_name', 'email')
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

        $contentPost->load(['user', 'client']);

        return Inertia::render('Content/Edit', [
            'contentPost' => [
                'id' => $contentPost->id,
                'client_id' => $contentPost->client_id,
                'title' => $contentPost->title,
                'description' => $contentPost->description,
                'platform' => $contentPost->platform,
                'content_type' => $contentPost->content_type,
                'content_url' => $contentPost->content_url,
                'post_count' => $contentPost->post_count,
                'scheduled_date' => $contentPost->scheduled_date,
                'published_date' => $contentPost->published_date,
                'status' => $contentPost->status,
                'content_category' => $contentPost->content_category,
                'tags' => $contentPost->tags,
                'notes' => $contentPost->notes,
                'engagement_metrics' => $contentPost->engagement_metrics,
                'engagement_rate' => $contentPost->engagement_rate,
                'total_engagement' => $contentPost->total_engagement,
            ],
            'clients' => $clients
        ]);
    }

    /**
     * Update the specified content post in storage.
     */
    public function update(Request $request, ContentPost $contentPost)
    {
        Gate::authorize('update', $contentPost);

        $validated = $request->validate([
            'client_id' => 'sometimes|required|exists:users,id',
            'platform' => 'sometimes|required|string|max:255',
            'content_type' => 'sometimes|required|string|max:255',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'content_url' => 'nullable|string|max:255',
            'post_count' => 'nullable|integer|min:1',
            'scheduled_date' => 'nullable|date',
            'published_date' => 'nullable|date',
            'status' => 'sometimes|required|in:draft,scheduled,published,archived',
            'content_category' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'engagement_metrics' => 'nullable|array',
        ]);

        $contentPost->update($validated);

        return redirect()->route('content.index')
            ->with('success', 'Content post updated successfully');
    }

    /**
     * Remove the specified content post from storage.
     */
    public function destroy(ContentPost $contentPost)
    {
        Gate::authorize('delete', $contentPost);

        $contentPost->delete();

        return redirect()->route('content.index')
            ->with('success', 'Content post deleted successfully');
    }

    /**
     * Get content posts statistics
     */
    public function statistics(Request $request)
    {
        Gate::authorize('viewAny', ContentPost::class);

        $query = ContentPost::query();

        // Role-based filtering
        if (Auth::user()->role === 'va') {
            $query->where('user_id', Auth::id());
        }

        $totalPosts = $query->count();
        $publishedPosts = $query->where('status', 'published')->count();
        $scheduledPosts = $query->where('status', 'scheduled')->count();
        $draftPosts = $query->where('status', 'draft')->count();
        $archivedPosts = $query->where('status', 'archived')->count();

        $monthlyPosts = $query->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month');

        $platformStats = $query->selectRaw('platform, COUNT(*) as total')
            ->groupBy('platform')
            ->pluck('total', 'platform');

        $contentTypeStats = $query->selectRaw('content_type, COUNT(*) as total')
            ->groupBy('content_type')
            ->pluck('total', 'content_type');

        return Inertia::render('Content/Statistics', [
            'statistics' => [
                'total_posts' => $totalPosts,
                'published_posts' => $publishedPosts,
                'scheduled_posts' => $scheduledPosts,
                'draft_posts' => $draftPosts,
                'archived_posts' => $archivedPosts,
                'monthly_posts' => $monthlyPosts,
                'platform_stats' => $platformStats,
                'content_type_stats' => $contentTypeStats,
            ]
        ]);
    }
}
