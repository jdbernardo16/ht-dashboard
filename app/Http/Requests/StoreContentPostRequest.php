<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreContentPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $maxFileSize = config('validation.file_upload.max_size', 10 * 1024 * 1024);
        $maxFiles = config('validation.file_upload.max_files', 10);
        $allowedImages = config('validation.file_upload.allowed_images', ['jpeg', 'png', 'jpg', 'gif', 'webp']);
        $allowedDocuments = config('validation.file_upload.allowed_documents', ['pdf', 'xlsx', 'csv']);
        $maxDimensions = config('validation.file_upload.max_image_dimensions', [4000, 4000]);
        $minDimensions = config('validation.file_upload.min_image_dimensions', [10, 10]);

        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'content_url' => 'required|url|max:255',
            'content_type' => 'required|string|in:post,story,reel,video,image,carousel,live,article',
            'platform' => 'nullable|array',
            'status' => 'required|string|in:draft,scheduled,published',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'published_date' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
            'category_id' => 'nullable|exists:categories,id',
            'content_category' => 'nullable|in:Social Media Post,Blog Article,Email Newsletter,Product Description,Marketing Campaign,Press Release,Video Content,Other',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string|max:2000',
            'meta_description' => 'nullable|string|max:255',
            'seo_keywords' => 'nullable|string|max:255',
            
            // Image validation
            'image' => [
                'nullable',
                'file',
                'image',
                "max:{$maxFileSize}",
                'mimes:' . implode(',', $allowedImages),
                "dimensions:min_width={$minDimensions[0]},min_height={$minDimensions[1]},max_width={$maxDimensions[0]},max_height={$maxDimensions[1]}"
            ],
            
            // Media files validation
            'media' => [
                'nullable',
                'array',
                "max:{$maxFiles}"
            ],
            'media.*' => [
                'required',
                'file',
                "max:{$maxFileSize}",
                function ($attribute, $value, $fail) use ($allowedImages, $allowedDocuments) {
                    // Get file extension
                    $extension = strtolower($value->getClientOriginalExtension());
                    
                    // Check if it's an allowed file type
                    $allowedTypes = array_merge($allowedImages, $allowedDocuments);
                    
                    if (!in_array($extension, $allowedTypes)) {
                        $fail("The :attribute must be one of: " . implode(', ', $allowedTypes) . ".");
                    }
                    
                    // Additional MIME type validation for security
                    $allowedMimes = [
                        'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp',
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv'
                    ];
                    
                    if (!in_array($value->getMimeType(), $allowedMimes)) {
                        $fail('The :attribute has an invalid file type.');
                    }
                }
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $maxFileSizeMB = config('validation.file_upload.max_size', 10 * 1024 * 1024) / 1024 / 1024;
        $maxFiles = config('validation.file_upload.max_files', 10);
        $maxDimensions = config('validation.file_upload.max_image_dimensions', [4000, 4000]);
        $minDimensions = config('validation.file_upload.min_image_dimensions', [100, 100]);
        $allowedImages = config('validation.file_upload.allowed_images', ['jpeg', 'png', 'jpg', 'gif', 'webp']);

        return [
            'title.required' => 'A title is required.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'description.required' => 'Description is required.',
            'description.max' => 'Description cannot exceed 5000 characters.',
            'content_url.required' => 'Content URL is required.',
            'content_url.url' => 'Content URL must be a valid URL.',
            'content_url.max' => 'Content URL cannot exceed 255 characters.',
            'content_type.required' => 'Content type is required.',
            'content_type.in' => 'Invalid content type selected.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'scheduled_date.required' => 'Scheduled date is required.',
            'scheduled_date.after_or_equal' => 'Scheduled date must be today or in the future.',
            'published_date.required' => 'Published date is required.',
            'published_date.date' => 'Published date must be a valid date.',
            'client_id.exists' => 'Selected client does not exist.',
            'category_id.exists' => 'Selected category does not exist.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
            'notes.max' => 'Notes cannot exceed 2000 characters.',
            'meta_description.max' => 'Meta description cannot exceed 255 characters.',
            'seo_keywords.max' => 'SEO keywords cannot exceed 255 characters.',
            
            // Image validation messages
            'image.required' => 'An image is required.',
            'image.file' => 'The image must be a file.',
            'image.image' => 'The file must be an image.',
            'image.max' => "Image size must be less than {$maxFileSizeMB}MB.",
            'image.mimes' => "Image must be a " . implode(', ', array_map('strtoupper', $allowedImages)) . " file.",
            'image.dimensions' => "Image dimensions must be between {$minDimensions[0]}x{$minDimensions[1]} and {$maxDimensions[0]}x{$maxDimensions[1]} pixels.",
            
            // Media files validation messages
            'media.max' => "You can upload maximum {$maxFiles} media files.",
            'media.*.required' => 'Each media file is required.',
            'media.*.file' => 'Invalid file format.',
            'media.*.max' => "Each file must be less than {$maxFileSizeMB}MB.",
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'image' => 'main image',
            'media.*' => 'media file',
            'scheduled_date' => 'scheduled date',
            'content_type' => 'content type',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Handle platform JSON string conversion
        if ($this->has('platform') && is_string($this->input('platform'))) {
            try {
                $this->merge([
                    'platform' => json_decode($this->input('platform'), true, 512, JSON_THROW_ON_ERROR)
                ]);
            } catch (\JsonException) {
                $this->merge(['platform' => []]);
            }
        }

        // Handle tags JSON string conversion
        if ($this->has('tags') && is_string($this->input('tags'))) {
            try {
                $this->merge([
                    'tags' => json_decode($this->input('tags'), true, 512, JSON_THROW_ON_ERROR)
                ]);
            } catch (\JsonException) {
                $this->merge(['tags' => []]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom business logic validation
            if ($this->input('status') === 'scheduled' && !$this->input('scheduled_date')) {
                $validator->errors()->add('scheduled_date', 'Scheduled date is required for scheduled posts.');
            }

            if (in_array($this->input('content_type'), ['post', 'story', 'reel', 'image', 'carousel', 'live']) && empty($this->input('platform'))) {
                $validator->errors()->add('platform', 'Platform is required for social media content types.');
            }

            // Validate that at least one file is provided for certain content types
            if (in_array($this->input('content_type'), ['post', 'story', 'reel', 'image', 'carousel', 'live']) &&
                !$this->hasFile('image') &&
                !$this->hasFile('media')) {
                $validator->errors()->add('files', 'At least one image or media file is required for this content type.');
            }
        });
    }
}