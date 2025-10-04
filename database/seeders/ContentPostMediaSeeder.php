<?php

namespace Database\Seeders;

use App\Models\ContentPost;
use App\Models\User;
use App\Models\ContentPostMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ContentPostMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all content posts and users
        $contentPosts = ContentPost::all();
        $users = User::all();
        
        if ($contentPosts->isEmpty()) {
            $this->command->warn('No content posts found. Please run ContentPostSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Sample social media content files
        $sampleFiles = [
            [
                'file_name' => 'instagram_post_1.jpg',
                'file_path' => 'content/instagram/instagram_post_1.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 512000,
                'original_name' => 'sports_card_collection.jpg',
                'description' => 'Featured sports card collection for Instagram'
            ],
            [
                'file_name' => 'facebook_banner.png',
                'file_path' => 'content/facebook/facebook_banner.png',
                'mime_type' => 'image/png',
                'file_size' => 786432,
                'original_name' => 'weekly_specials_banner.png',
                'description' => 'Facebook banner for weekly specials'
            ],
            [
                'file_name' => 'tiktok_video.mp4',
                'file_path' => 'content/tiktok/tiktok_video.mp4',
                'mime_type' => 'video/mp4',
                'file_size' => 5242880,
                'original_name' => 'card_unboxing_video.mp4',
                'description' => 'TikTok video of card unboxing'
            ],
            [
                'file_name' => 'twitter_image.gif',
                'file_path' => 'content/twitter/twitter_image.gif',
                'mime_type' => 'image/gif',
                'file_size' => 1024000,
                'original_name' => 'rare_card_showcase.gif',
                'description' => 'Animated GIF showcasing rare card'
            ],
            [
                'file_name' => 'youtube_thumbnail.jpg',
                'file_path' => 'content/youtube/youtube_thumbnail.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 256000,
                'original_name' => 'video_thumbnail.jpg',
                'description' => 'YouTube video thumbnail'
            ],
            [
                'file_name' => 'linkedin_document.pdf',
                'file_path' => 'content/linkedin/linkedin_document.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 409600,
                'original_name' => 'market_analysis_report.pdf',
                'description' => 'LinkedIn market analysis document'
            ],
            [
                'file_name' => 'pinterest_infographic.png',
                'file_path' => 'content/pinterest/pinterest_infographic.png',
                'mime_type' => 'image/png',
                'file_size' => 1048576,
                'original_name' => 'card_grading_guide.png',
                'description' => 'Pinterest infographic about card grading'
            ],
            [
                'file_name' => 'email_newsletter.html',
                'file_path' => 'content/email/email_newsletter.html',
                'mime_type' => 'text/html',
                'file_size' => 65536,
                'original_name' => 'monthly_newsletter.html',
                'description' => 'Email newsletter template'
            ]
        ];

        // Create media for about 70% of content posts
        $postsWithMedia = $contentPosts->random((int)($contentPosts->count() * 0.7));

        foreach ($postsWithMedia as $post) {
            // Each post can have 1-4 media files
            $mediaCount = rand(1, 4);
            
            for ($i = 0; $i < $mediaCount; $i++) {
                $fileData = Arr::random($sampleFiles);
                $user = $users->random();
                
                $media = [
                    'content_post_id' => $post->id,
                    'user_id' => $user->id,
                    'file_name' => $fileData['file_name'],
                    'file_path' => $fileData['file_path'],
                    'mime_type' => $fileData['mime_type'],
                    'file_size' => $fileData['file_size'],
                    'original_name' => $fileData['original_name'],
                    'description' => $fileData['description'],
                    'order' => $i + 1,
                    'is_primary' => $i === 0, // First file is primary
                    'created_at' => now()->subMinutes(rand(1, 2880)),
                    'updated_at' => now(),
                ];

                ContentPostMedia::create($media);
            }
        }
    }
}