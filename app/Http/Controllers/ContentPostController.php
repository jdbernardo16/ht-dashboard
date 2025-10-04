<?php

namespace App\Http\Controllers;

use App\Models\ContentPost;
use App\Models\ContentPostMedia;
use App\Http\Requests\StoreContentPostRequest;
use App\Http\Requests\UpdateContentPostRequest;
use App\Services\FileStorageService;
use App\Services\ImageService;
use App\Exceptions\FileUploadException;
use App\Exceptions\FileStorageException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ContentPostController extends Controller
{
    protected FileStorageService $fileStorageService;
    protected ImageService $imageService;

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
        Gate::authorize('viewAny', ContentPost::class);

        $query = ContentPost::with(['client', 'category', 'media']);

        // Role-based filtering
        if (Auth::user()->role === 'va') {
            $query->where('user_id', Auth::id());
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by content type
        if ($request->has('content_type') && $request->content_type !== 'all') {
            $query->where('content_type', $request->content_type);
        }

        // Filter by client
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('content_category', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Date range filtering
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $contentPosts = $query->paginate(15)->withQueryString();

        // Get filter data
        $clients = Auth::user()->role !== 'va'
            ? \App\Models\Client::orderByRaw("CONCAT(first_name, ' ', last_name) ASC")->get()
            : collect([]);

        return inertia('Content/Index', [
            'contentPosts' => $contentPosts,
            'filters' => $request->only(['status', 'content_type', 'client_id', 'search', 'date_from', 'date_to', 'sort_by', 'sort_order']),
            'clients' => $clients,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', ContentPost::class);

        $clients = Auth::user()->role !== 'va'
            ? \App\Models\Client::orderByRaw("CONCAT(first_name, ' ', last_name) ASC")->get()
            : collect([]);
            
        $categories = \App\Models\Category::orderBy('name')->get();

        return inertia('Content/Create', [
            'clients' => $clients,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContentPostRequest $request)
    {
        $validated = $request->validated();
        $imagePath = null;
        $mediaPaths = [];

        try {
            // Handle main image upload
            if ($request->hasFile('image')) {
                $imagePath = $this->processImage($request->file('image'));
            }

            // Create content post
            $contentPost = ContentPost::create([
                'user_id' => Auth::id(),
                'client_id' => $validated['client_id'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'platform' => $validated['platform'] ?? [],
                'content_type' => $validated['content_type'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'image' => $imagePath,
                'content_category' => $validated['content_category'] ?? null,
                'tags' => $validated['tags'] ?? [],
                'notes' => $validated['notes'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'seo_keywords' => $validated['seo_keywords'] ?? null,
                'status' => $validated['status'],
                'scheduled_date' => $validated['scheduled_date'] ?? null,
                'content_url' => $validated['content_url'] ?? null,
                'published_date' => $validated['published_date'] ?? null,
                'post_count' => $validated['post_count'] ?? 0,
            ]);

            // Handle media files
            if ($request->hasFile('media')) {
                $mediaPaths = $this->processMediaFiles($request->file('media'), $contentPost->id);
            }

            Log::info('ContentPost created successfully', [
                'content_post_id' => $contentPost->id,
                'title' => $contentPost->title,
                'has_image' => !is_null($imagePath),
                'media_count' => count($mediaPaths)
            ]);

            return redirect()
                ->route('content.web.show', $contentPost)
                ->with('success', 'Content post created successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to create content post', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up uploaded files on error
            $this->cleanupUploadedFiles($imagePath, $mediaPaths);

            throw new FileUploadException(
                'Failed to create content post',
                ['original_error' => $e->getMessage()],
                'content_post_creation'
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContentPost $content_post)
    {
        Gate::authorize('view', $content_post);

        $content_post->load(['client', 'category', 'user', 'media' => function ($query) {
            $query->orderBy('order');
        }]);

        return inertia('Content/Show', [
            'contentPost' => $content_post,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContentPost $content_post)
    {
        Gate::authorize('update', $content_post);

        $content_post->load(['media' => function ($query) {
            $query->orderBy('order');
        }]);

        $clients = Auth::user()->role !== 'va'
            ? \App\Models\Client::orderByRaw("CONCAT(first_name, ' ', last_name) ASC")->get()
            : collect([]);
            
        $categories = \App\Models\Category::orderBy('name')->get();

        return inertia('Content/Edit', [
            'contentPost' => $content_post,
            'clients' => $clients,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContentPostRequest $request, ContentPost $content_post)
    {
        Gate::authorize('update', $content_post);
        
        $validated = $request->validated();
        $oldImagePath = $content_post->image;
        $newImagePath = $oldImagePath; // Keep existing by default
        $newMediaPaths = [];

        try {
            // Handle main image replacement
            if ($request->hasFile('image')) {
                $newImagePath = $this->processImage($request->file('image'));
            }

            // Handle new media files
            if ($request->hasFile('media')) {
                $newMediaPaths = $this->processMediaFiles($request->file('media'), $content_post->id);
            }

            // Handle media file removal
            if ($request->has('remove_media')) {
                $this->removeMediaFiles($request->input('remove_media'), $content_post);
            }

            // Update content post
            $content_post->update([
                'client_id' => $validated['client_id'] ?? $content_post->client_id,
                'category_id' => $validated['category_id'] ?? $content_post->category_id,
                'platform' => $validated['platform'] ?? $content_post->platform,
                'content_type' => $validated['content_type'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? $content_post->description,
                'image' => $newImagePath,
                'content_category' => $validated['content_category'] ?? $content_post->content_category,
                'tags' => $validated['tags'] ?? $content_post->tags,
                'notes' => $validated['notes'] ?? $content_post->notes,
                'meta_description' => $validated['meta_description'] ?? $content_post->meta_description,
                'seo_keywords' => $validated['seo_keywords'] ?? $content_post->seo_keywords,
                'status' => $validated['status'],
                'scheduled_date' => $validated['scheduled_date'] ?? $content_post->scheduled_date,
                'content_url' => $validated['content_url'] ?? $content_post->content_url,
                'published_date' => $validated['published_date'] ?? $content_post->published_date,
                'post_count' => $validated['post_count'] ?? $content_post->post_count,
            ]);

            // Delete old image if it was replaced
            if ($oldImagePath && $newImagePath !== $oldImagePath) {
                $this->imageService->deleteImage($oldImagePath);
            }

            Log::info('Content post updated successfully', [
                'content_post_id' => $content_post->id,
                'title' => $content_post->title,
                'image_updated' => $newImagePath !== $oldImagePath,
                'new_media_count' => count($newMediaPaths)
            ]);

            return redirect()
                ->route('content.web.show', $content_post)
                ->with('success', 'Content post updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update content post', [
                'content_post_id' => $content_post->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up newly uploaded files on error
            $this->cleanupUploadedFiles(
                $newImagePath !== $oldImagePath ? $newImagePath : null,
                $newMediaPaths
            );

            throw new FileUploadException(
                'Failed to update content post',
                ['original_error' => $e->getMessage()],
                'content_post_update'
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContentPost $content_post)
    {
        Gate::authorize('delete', $content_post);

        try {
            // Clean up associated images
            if ($content_post->image) {
                $this->imageService->deleteImage($content_post->image);
            }

            // Clean up associated media files
            $mediaFiles = $content_post->media->pluck('file_path')->toArray();
            if (!empty($mediaFiles)) {
                $this->imageService->deleteImages($mediaFiles);
            }

            $content_post->delete();

            Log::info('ContentPost deleted successfully', ['id' => $content_post->id]);

            return redirect()
                ->route('content.web.index')
                ->with('success', 'Content post deleted successfully');

        } catch (\Exception $e) {
            Log::error('ContentPost deletion failed', [
                'error' => $e->getMessage(),
                'content_post_id' => $content_post->id
            ]);

            return back()->withErrors([
                'delete' => 'Failed to delete content post: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process image upload
     */
    protected function processImage($file): string
    {
        try {
            $result = $this->fileStorageService->storeImage($file);
            return $result['path'];
        } catch (\Exception $e) {
            Log::error('Failed to process image', [
                'original_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            throw new FileStorageException(
                $file->getClientOriginalName(),
                'image_processing',
                'Failed to process image: ' . $e->getMessage(),
                $e
            );
        }
    }

    /**
     * Process media files upload
     */
    protected function processMediaFiles(array $files, int $contentPostId): array
    {
        $paths = [];
        
        foreach ($files as $index => $file) {
            try {
                $result = $this->fileStorageService->storeMediaFile($file);
                $paths[] = $result['path'];
                
                // Create media record
                ContentPostMedia::create([
                    'content_post_id' => $contentPostId,
                    'user_id' => Auth::id(),
                    'file_name' => $result['filename'],
                    'file_path' => $result['path'],
                    'mime_type' => $result['mime_type'],
                    'file_size' => $result['file_size'],
                    'original_name' => $result['original_name'],
                    'order' => $index,
                    'is_primary' => false,
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to process media file', [
                    'original_name' => $file->getClientOriginalName(),
                    'index' => $index,
                    'error' => $e->getMessage()
                ]);
                
                // Continue processing other files but log the error
                continue;
            }
        }
        
        return $paths;
    }

    /**
     * Remove media files
     */
    protected function removeMediaFiles(array $mediaIds, ContentPost $content_post): void
    {
        foreach ($mediaIds as $mediaId) {
            $media = $content_post->media()->find($mediaId);
            if ($media) {
                // Delete file from storage
                $this->fileStorageService->deleteFile($media->file_path);
                
                // Delete database record
                $media->delete();
            }
        }
    }

    /**
     * Clean up uploaded files on error
     */
    protected function cleanupUploadedFiles(?string $imagePath, array $mediaPaths): void
    {
        // Clean up main image
        if ($imagePath) {
            try {
                $this->imageService->deleteImage($imagePath);
            } catch (\Exception $e) {
                Log::error('Failed to cleanup image', [
                    'path' => $imagePath,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Clean up media files
        foreach ($mediaPaths as $path) {
            try {
                $this->fileStorageService->deleteFile($path);
            } catch (\Exception $e) {
                Log::error('Failed to cleanup media file', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
            }
        }
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

        return response()->json([
            'total_posts' => $totalPosts,
            'published_posts' => $publishedPosts,
            'scheduled_posts' => $scheduledPosts,
            'draft_posts' => $draftPosts,
        ]);
    }
}
