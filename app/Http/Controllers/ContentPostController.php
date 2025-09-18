<?php

namespace App\Http\Controllers;

use App\Models\ContentPost;
use App\Models\ContentPostMedia;
use App\Models\User;
use App\Services\ImageService;
use App\Services\FileValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ContentPostController extends Controller
{
    protected ImageService $imageService;
    protected FileValidationService $fileValidationService;

    public function __construct(ImageService $imageService, FileValidationService $fileValidationService)
    {
        $this->imageService = $imageService;
        $this->fileValidationService = $fileValidationService;
    }

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
            $query->platform($request->get('platform'));
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
                'image' => $post->image,
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

        \Log::info('ContentPost store method called', [
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'has_files' => $request->hasFile('image') || $request->hasFile('media'),
            'all_input_keys' => array_keys($request->all()),
            'files_keys' => array_keys($request->allFiles()),
            'raw_files' => $_FILES ?? 'No $_FILES',
            'request_all' => $request->all(),
            'image_input_value' => $request->input('image'),
            'has_image_input' => $request->has('image'),
            'image_file_info' => $request->hasFile('image') ? (
                is_array($request->file('image')) ?
                'Image is array: ' . json_encode(array_map(function($file) {
                    return [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'path' => $file->getPathname(),
                        'is_valid' => $file->isValid(),
                        'error' => $file->getError(),
                        'getimagesize' => @getimagesize($file->getPathname()) ?: 'Failed to get image size'
                    ];
                }, $request->file('image'))) :
                [
                    'original_name' => $request->file('image')->getClientOriginalName(),
                    'mime_type' => $request->file('image')->getMimeType(),
                    'size' => $request->file('image')->getSize(),
                    'path' => $request->file('image')->getPathname(),
                    'is_valid' => $request->file('image')->isValid(),
                    'error' => $request->file('image')->getError(),
                    'getimagesize' => @getimagesize($request->file('image')->getPathname()) ?: 'Failed to get image size'
                ]
            ) : 'No image file'
        ]);

        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:users,id',
                'platform' => 'required|array',
                'platform.*' => 'string|in:website,facebook,instagram,twitter,linkedin,tiktok,youtube,pinterest,email,other',
                'content_type' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'content_url' => 'nullable|string|max:255',
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max for images (will be optimized)
                'post_count' => 'nullable|integer|min:1',
                'scheduled_date' => 'nullable|date',
                'published_date' => 'nullable|date',
                'status' => 'required|in:draft,scheduled,published,archived',
                'content_category' => 'nullable|string|max:255',
                'tags' => 'nullable',
                'tags.*' => 'nullable|string',
                'notes' => 'nullable|string',
                'engagement_metrics' => 'nullable|array',
                'media' => 'nullable|array',
                'media.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,txt|max:10240', // 10MB max
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('ContentPost validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'files_data' => $request->allFiles()
            ]);
            throw $e;
        }

        $validated['user_id'] = Auth::id();

        // Remove image from validated data since it's a file upload
        $image = $validated['image'] ?? null;
        unset($validated['image']);

        $contentPost = ContentPost::create($validated);

        // Process image upload if any
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');

            // Handle case where image might be an array (from FileUpload component)
            if (is_array($imageFile)) {
                $imageFile = $imageFile[0]; // Take the first file
                \Log::info('Image upload detected as array, using first file', [
                    'file_name' => $imageFile->getClientOriginalName(),
                    'file_size' => $imageFile->getSize(),
                    'mime_type' => $imageFile->getMimeType()
                ]);
            } else {
                \Log::info('Image upload detected as single file', [
                    'file_name' => $imageFile->getClientOriginalName(),
                    'file_size' => $imageFile->getSize(),
                    'mime_type' => $imageFile->getMimeType()
                ]);
            }

            try {
                $imageResult = $this->imageService->processImage(
                    $imageFile,
                    'content_images',
                    [
                        'max_width' => 1920,
                        'max_height' => 1080,
                        'quality' => 85,
                        'create_thumbnail' => true,
                        'thumbnail_size' => 300,
                    ]
                );

                $updateResult = $contentPost->update(['image' => $imageResult['path']]);
                \Log::info('ContentPost image field updated with processed image', [
                    'success' => $updateResult,
                    'path' => $imageResult['path'],
                    'original_dimensions' => $imageResult['width'] . 'x' . $imageResult['height']
                ]);
            } catch (\Exception $e) {
                $fileName = is_array($request->file('image')) ?
                    $request->file('image')[0]->getClientOriginalName() :
                    $request->file('image')->getClientOriginalName();

                \Log::error('Image processing failed in store method', [
                    'error' => $e->getMessage(),
                    'file' => $fileName
                ]);

                // Return user-friendly error message
                return back()->withErrors([
                    'image' => 'Failed to process image: ' . $e->getMessage()
                ])->withInput();
            }
        } else {
            \Log::info('No image file detected in store method request');
        }

        // Process media uploads if any
        if ($request->hasFile('media')) {
            $this->processMediaUploads($request, $contentPost);
        }

        return redirect()->route('content.web.index')
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
                'image' => $contentPost->image,
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

        $contentPost->load(['user', 'client', 'media']);

        return Inertia::render('Content/Edit', [
            'contentPost' => [
                'id' => $contentPost->id,
                'client_id' => $contentPost->client_id,
                'title' => $contentPost->title,
                'description' => $contentPost->description,
                'platform' => $contentPost->platform,
                'content_type' => $contentPost->content_type,
                'content_url' => $contentPost->content_url,
                'image' => $contentPost->image,
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
                'media' => $contentPost->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'file_name' => $media->file_name,
                        'file_path' => $media->file_path,
                        'url' => $media->url,
                        'mime_type' => $media->mime_type,
                        'file_size' => $media->file_size,
                        'original_name' => $media->original_name,
                        'is_primary' => $media->is_primary,
                        'order' => $media->order,
                    ];
                }),
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

        \Log::info('ContentPost update method called', [
            'content_post_id' => $contentPost->id,
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'has_files' => $request->hasFile('image') || $request->hasFile('media'),
            'all_input_keys' => array_keys($request->all()),
            'files_keys' => array_keys($request->allFiles()),
            'raw_files' => $_FILES ?? 'No $_FILES',
            'image_file_info' => $request->hasFile('image') ? (
                is_array($request->file('image')) ?
                'Image is array: ' . json_encode(array_map(function($file) {
                    return [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'path' => $file->getPathname(),
                        'is_valid' => $file->isValid(),
                        'error' => $file->getError(),
                        'getimagesize' => @getimagesize($file->getPathname()) ?: 'Failed to get image size'
                    ];
                }, $request->file('image'))) :
                [
                    'original_name' => $request->file('image')->getClientOriginalName(),
                    'mime_type' => $request->file('image')->getMimeType(),
                    'size' => $request->file('image')->getSize(),
                    'path' => $request->file('image')->getPathname(),
                    'is_valid' => $request->file('image')->isValid(),
                    'error' => $request->file('image')->getError(),
                    'getimagesize' => @getimagesize($request->file('image')->getPathname()) ?: 'Failed to get image size'
                ]
            ) : 'No image file'
        ]);

        try {
            $validated = $request->validate([
                'client_id' => 'sometimes|required|exists:users,id',
                'platform' => 'sometimes|required|array',
                'platform.*' => 'string|in:website,facebook,instagram,twitter,linkedin,tiktok,youtube,pinterest,email,other',
                'content_type' => 'sometimes|required|string|max:255',
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'content_url' => 'nullable|string|max:255',
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max for images (will be optimized)
                'post_count' => 'nullable|integer|min:1',
                'scheduled_date' => 'nullable|date',
                'published_date' => 'nullable|date',
                'status' => 'sometimes|required|in:draft,scheduled,published,archived',
                'content_category' => 'nullable|string|max:255',
                'tags' => 'nullable',
                'notes' => 'nullable|string',
                'engagement_metrics' => 'nullable|array',
                'media' => 'nullable|array',
                'media.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,txt|max:10240', // 10MB max
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('ContentPost validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'files_data' => $request->allFiles()
            ]);
            throw $e;
        }

        $oldStatus = $contentPost->status;
        
        // Remove image from validated data since it's a file upload
        $image = $validated['image'] ?? null;
        unset($validated['image']);
        
        // Handle tags JSON string conversion
        if (isset($validated['tags']) && is_string($validated['tags'])) {
            try {
                $validated['tags'] = json_decode($validated['tags'], true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                // If JSON decoding fails, treat as comma-separated string
                $validated['tags'] = array_filter(array_map('trim', explode(',', $validated['tags'])));
            }
        }
        
        $contentPost->update($validated);

        // Process image upload if any
        if ($request->hasFile('image')) {
            \Log::info('Image upload detected in update method', [
                'file_name' => $request->file('image')->getClientOriginalName(),
                'file_size' => $request->file('image')->getSize(),
                'mime_type' => $request->file('image')->getMimeType(),
                'old_image' => $contentPost->image
            ]);

            try {
                // Delete old image if exists
                if ($contentPost->image) {
                    $deleteResult = $this->imageService->deleteImage($contentPost->image);
                    \Log::info('Old image deletion attempted', [
                        'path' => $contentPost->image,
                        'success' => $deleteResult
                    ]);
                }

                $imageResult = $this->imageService->processImage(
                    $request->file('image'),
                    'content_images',
                    [
                        'max_width' => 1920,
                        'max_height' => 1080,
                        'quality' => 85,
                        'create_thumbnail' => true,
                        'thumbnail_size' => 300,
                    ]
                );

                $updateResult = $contentPost->update(['image' => $imageResult['path']]);
                \Log::info('ContentPost image field updated with processed image', [
                    'success' => $updateResult,
                    'path' => $imageResult['path'],
                    'original_dimensions' => $imageResult['width'] . 'x' . $imageResult['height']
                ]);
            } catch (\Exception $e) {
                \Log::error('Image processing failed in update method', [
                    'error' => $e->getMessage(),
                    'file' => $request->file('image')->getClientOriginalName()
                ]);

                // Return user-friendly error message
                return back()->withErrors([
                    'image' => 'Failed to process image: ' . $e->getMessage()
                ])->withInput();
            }
        } else {
            \Log::info('No image file detected in update method request');
        }

        // Process media uploads if any
        if ($request->hasFile('media')) {
            $this->processMediaUploads($request, $contentPost);
        }

        // Send notification if status changed to published
        if ($oldStatus !== 'published' && $contentPost->status === 'published') {
            $platform = !empty($contentPost->platform) ? implode(', ', $contentPost->platform) : 'multiple platforms';
            \App\Services\NotificationService::sendContentPublication(
                $contentPost->user,
                $contentPost->title,
                $platform,
                ['content_id' => $contentPost->id]
            );
        }

        return redirect()->route('content.web.show', $contentPost->id)
            ->with('success', 'Content post updated successfully');
    }

    /**
     * Remove the specified content post from storage.
     */
    public function destroy(ContentPost $contentPost)
    {
        Gate::authorize('delete', $contentPost);

        try {
            // Clean up associated images
            if ($contentPost->image) {
                $this->imageService->deleteImage($contentPost->image);
                \Log::info('ContentPost image cleaned up during deletion', ['path' => $contentPost->image]);
            }

            // Clean up associated media files
            $mediaFiles = $contentPost->media->pluck('file_path')->toArray();
            if (!empty($mediaFiles)) {
                $deleteResults = $this->imageService->deleteImages($mediaFiles);
                \Log::info('ContentPost media files cleaned up during deletion', [
                    'files' => $mediaFiles,
                    'results' => $deleteResults
                ]);
            }

            $contentPost->delete();

            \Log::info('ContentPost deleted successfully', ['id' => $contentPost->id]);

            return redirect()->route('content.web.index')
                ->with('success', 'Content post deleted successfully');

        } catch (\Exception $e) {
            \Log::error('ContentPost deletion failed', [
                'error' => $e->getMessage(),
                'content_post_id' => $contentPost->id
            ]);

            return back()->withErrors([
                'delete' => 'Failed to delete content post: ' . $e->getMessage()
            ]);
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
        $archivedPosts = $query->where('status', 'archived')->count();

        $monthlyPosts = $query->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month');

        // Get all posts and manually count platforms
        $allPosts = $query->get();
        $platformStats = [];
        
        $allPlatforms = ['website', 'facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube', 'pinterest', 'email', 'other'];
        
        foreach ($allPlatforms as $platform) {
            $platformStats[$platform] = $allPosts->filter(function ($post) use ($platform) {
                return in_array($platform, $post->platform ?? []);
            })->count();
        }
        
        // Remove platforms with zero counts
        $platformStats = array_filter($platformStats);

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

    /**
     * Process media file uploads for a content post
     */
    protected function processMediaUploads(Request $request, ContentPost $contentPost)
    {
        $uploadedFiles = $request->file('media');
        $order = 0;

        foreach ($uploadedFiles as $file) {
            try {
                // Validate file using FileValidationService
                $validationResult = $this->fileValidationService->validate($file, $this->determineFileType($file));
                
                if (!$validationResult['valid']) {
                    \Log::warning('Media file validation failed', [
                        'file' => $file->getClientOriginalName(),
                        'errors' => $validationResult['errors'],
                        'content_post_id' => $contentPost->id
                    ]);
                    continue; // Skip invalid files but continue processing others
                }

                // Check if it's an image file
                $mimeType = $file->getMimeType();
                $isImage = str_starts_with($mimeType, 'image/');

                if ($isImage) {
                    // Process image with ImageService
                    $imageResult = $this->imageService->processImage(
                        $file,
                        'content_post_media',
                        [
                            'max_width' => 1920,
                            'max_height' => 1080,
                            'quality' => 85,
                            'create_thumbnail' => true,
                            'thumbnail_size' => 300,
                        ]
                    );

                    // Create media record with processed image info
                    ContentPostMedia::create([
                        'content_post_id' => $contentPost->id,
                        'user_id' => Auth::id(),
                        'file_name' => $imageResult['filename'],
                        'file_path' => $imageResult['path'],
                        'mime_type' => $imageResult['mime_type'],
                        'file_size' => $imageResult['size'],
                        'original_name' => $imageResult['original_name'],
                        'order' => $order++,
                        'is_primary' => $order === 1, // First file is primary
                    ]);

                    \Log::info('Media image processed and stored', [
                        'content_post_id' => $contentPost->id,
                        'file_name' => $imageResult['filename'],
                        'dimensions' => $imageResult['width'] . 'x' . $imageResult['height']
                    ]);
                } else {
                    // Handle non-image files (PDF, DOC, etc.) with basic storage
                    $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('content_post_media', $fileName, 'public');

                    // Create media record
                    ContentPostMedia::create([
                        'content_post_id' => $contentPost->id,
                        'user_id' => Auth::id(),
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'order' => $order++,
                        'is_primary' => $order === 1, // First file is primary
                    ]);

                    \Log::info('Media file stored', [
                        'content_post_id' => $contentPost->id,
                        'file_name' => $fileName,
                        'file_size' => $file->getSize()
                    ]);
                }

            } catch (\Exception $e) {
                \Log::error('Media file processing failed', [
                    'error' => $e->getMessage(),
                    'file' => $file->getClientOriginalName(),
                    'content_post_id' => $contentPost->id
                ]);

                // Continue processing other files but log the error
                continue;
            }
        }
    }

    /**
     * Determine file type based on MIME type
     */
    protected function determineFileType($file): string
    {
        $mimeType = $file->getMimeType();
        
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif ($mimeType === 'application/pdf') {
            return 'pdf';
        } elseif (in_array($mimeType, [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel'
        ])) {
            return 'xlsx';
        } elseif ($mimeType === 'text/csv') {
            return 'csv';
        } else {
            return 'file'; // fallback
        }
    }
}
