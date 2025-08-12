<?php

namespace App\Http\Controllers;

use App\Models\ContentPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ContentPostController extends Controller
{
    /**
     * Display a listing of the content posts.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ContentPost::class);

        $query = ContentPost::with(['user', 'category']);

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

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Role-based filtering
        if (Auth::user()->hasRole('virtual_assistant')) {
            $query->where('user_id', Auth::id());
        }

        $posts = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json($posts);
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
            'type' => 'required|in:blog,social_media,email,newsletter',
            'status' => 'required|in:draft,published,scheduled',
            'category_id' => 'nullable|exists:categories,id',
            'scheduled_at' => 'nullable|date|required_if:status,scheduled',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'featured_image' => 'nullable|image|max:2048',
            'meta_description' => 'nullable|string|max:160',
            'seo_keywords' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('content_images', 'public');
            $validated['featured_image'] = $path;
        }

        // Handle tags
        if (isset($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }

        $post = ContentPost::create($validated);

        return response()->json([
            'message' => 'Content post created successfully',
            'data' => $post->load(['user', 'category'])
        ], 201);
    }

    /**
     * Display the specified content post.
     */
    public function show(ContentPost $contentPost)
    {
        Gate::authorize('view', $contentPost);

        return response()->json($contentPost->load(['user', 'category']));
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
            'type' => 'sometimes|required|in:blog,social_media,email,newsletter',
            'status' => 'sometimes|required|in:draft,published,scheduled',
            'category_id' => 'nullable|exists:categories,id',
            'scheduled_at' => 'nullable|date|required_if:status,scheduled',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'featured_image' => 'nullable|image|max:2048',
            'meta_description' => 'nullable|string|max:160',
            'seo_keywords' => 'nullable|string|max:255',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($contentPost->featured_image) {
                Storage::disk('public')->delete($contentPost->featured_image);
            }
            $path = $request->file('featured_image')->store('content_images', 'public');
            $validated['featured_image'] = $path;
        }

        // Handle tags
        if (isset($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }

        $contentPost->update($validated);

        return response()->json([
            'message' => 'Content post updated successfully',
            'data' => $contentPost->load(['user', 'category'])
        ]);
    }

    /**
     * Remove the specified content post from storage.
     */
    public function destroy(ContentPost $contentPost)
    {
        Gate::authorize('delete', $contentPost);

        // Delete featured image if exists
        if ($contentPost->featured_image) {
            Storage::disk('public')->delete($contentPost->featured_image);
        }

        $contentPost->delete();

        return response()->json([
            'message' => 'Content post deleted successfully'
        ], 204);
    }

    /**
     * Get content statistics
     */
    public function statistics(Request $request)
    {
        Gate::authorize('viewAny', ContentPost::class);

        $query = ContentPost::query();

        // Role-based filtering
        if (Auth::user()->hasRole('virtual_assistant')) {
            $query->where('user_id', Auth::id());
        }

        $totalPosts = $query->count();
        $publishedPosts = $query->clone()->where('status', 'published')->count();
        $draftPosts = $query->clone()->where('status', 'draft')->count();
        $scheduledPosts = $query->clone()->where('status', 'scheduled')->count();

        $postsByType = $query->selectRaw('type, COUNT(*) as count')
                           ->groupBy('type')
                           ->pluck('count', 'type');

        return response()->json([
            'total_posts' => $totalPosts,
            'published_posts' => $publishedPosts,
            'draft_posts' => $draftPosts,
            'scheduled_posts' => $scheduledPosts,
            'posts_by_type' => $postsByType
        ]);
    }
}