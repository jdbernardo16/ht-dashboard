<?php

namespace App\Http\Controllers;

use App\Models\ContentPost;
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

        $query = ContentPost::with(['user']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Role-based filtering
        if (Auth::user()->role === 'va') {
            $query->where('user_id', Auth::id());
        }

        $contentPosts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('Content/Index', [
            'contentPosts' => $contentPosts,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    /**
     * Show the form for creating a new content post.
     */
    public function create()
    {
        Gate::authorize('create', ContentPost::class);

        return Inertia::render('Content/Create');
    }

    /**
     * Store a newly created content post in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', ContentPost::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,archived',
            'tags' => 'nullable|string',
            'meta_description' => 'nullable|string|max:255',
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

        return Inertia::render('Content/Show', [
            'contentPost' => $contentPost->load(['user'])
        ]);
    }

    /**
     * Show the form for editing the specified content post.
     */
    public function edit(ContentPost $contentPost)
    {
        Gate::authorize('update', $contentPost);

        return Inertia::render('Content/Edit', [
            'contentPost' => $contentPost->load(['user'])
        ]);
    }

    /**
     * Update the specified content post in storage.
     */
    public function update(Request $request, ContentPost $contentPost)
    {
        Gate::authorize('update', $contentPost);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:draft,published,archived',
            'tags' => 'nullable|string',
            'meta_description' => 'nullable|string|max:255',
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
        $draftPosts = $query->where('status', 'draft')->count();

        $monthlyPosts = $query->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month');

        return Inertia::render('Content/Statistics', [
            'statistics' => [
                'total_posts' => $totalPosts,
                'published_posts' => $publishedPosts,
                'draft_posts' => $draftPosts,
                'monthly_posts' => $monthlyPosts
            ]
        ]);
    }
}
