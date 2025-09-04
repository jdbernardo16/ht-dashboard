<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ContentPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ContentPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Make sure we have users & categories
        $users      = User::whereIn('role', ['admin', 'manager', 'va'])->get();
        $categories = Category::all();

        if ($users->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Skipping ContentPostSeeder – no users or categories found.');
            return;
        }

        // 2. Data pools
        $contentTypes = ['blog', 'social', 'email', 'video', 'podcast', 'infographic', 'case_study', 'newsletter'];
        $statuses     = ['draft', 'published', 'scheduled', 'archived'];
        $platforms    = ['website', 'facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok', 'email'];

        $blogTitles = [
            '10 Proven Strategies to Boost Your Online Sales',
            'The Ultimate Guide to Social Media Marketing in 2024',
            'How to Create a Winning Content Marketing Strategy',
            'Email Marketing Best Practices for Small Businesses',
            'SEO Tips That Actually Work in 2024',
            'The Future of Digital Marketing: Trends to Watch',
            'Building a Strong Brand Identity from Scratch',
            'Customer Retention Strategies That Drive Growth',
            'The Power of Video Marketing for Business Growth',
            'Creating High-Converting Landing Pages',
            'Social Media Advertising: A Complete Guide',
            'Content Marketing ROI: How to Measure Success',
            'Building an Email List That Actually Converts',
            'The Psychology of Online Sales',
            'Instagram Marketing Strategies for 2024',
            'LinkedIn Marketing for B2B Businesses',
            'Facebook Advertising Tips That Drive Results',
            'Google Ads Optimization Strategies',
            'Content Calendar Planning for Consistent Growth',
            'Marketing Automation Tools That Save Time',
        ];

        $socialContent = [
            'Behind the scenes of our latest project 🚀',
            'Monday motivation: Your weekly business tip 💡',
            'Client success story spotlight 🌟',
            'Quick tip: How to improve your website conversion',
            'Throwback Thursday: Our company journey',
            'Friday feature: Meet the team member of the week',
            'Weekend wisdom: Business lessons learned',
            'Product spotlight: New feature announcement',
            'Customer testimonial Tuesday',
            'Wednesday wisdom: Industry insights',
            'Throwback to our first client project',
            'Motivation Monday: Start your week right',
            'Feature Friday: Highlighting our services',
            'Success Saturday: Celebrating client wins',
            'Sunday strategy: Planning for the week ahead',
        ];

        $emailSubjects = [
            'Welcome to our community!',
            'Your weekly business insights',
            'Exclusive offer for our subscribers',
            'Monthly newsletter: Latest updates',
            'Thank you for your purchase!',
            'New blog post: Must-read insights',
            'Client spotlight: Success story',
            'Upcoming webinar invitation',
            'Product update announcement',
            'Seasonal promotion alert',
        ];

        // 3. Seed regular posts
        foreach ($users as $user) {
            $postsCount = rand(8, 12);

            for ($i = 0; $i < $postsCount; $i++) {
                $publishDate = Carbon::createFromTimestamp(
                    rand(
                        Carbon::now()->subMonths(3)->timestamp,
                        Carbon::now()->addWeeks(2)->timestamp
                    )
                );

                $createdDate = Carbon::createFromTimestamp(
                    rand(
                        Carbon::now()->subMonths(4)->timestamp,
                        $publishDate->timestamp - 86400
                    )
                );

                $type     = $contentTypes[array_rand($contentTypes)];
                $status   = $statuses[array_rand($statuses)];
                // Select 1-3 random platforms for each content post
                $selectedPlatforms = array_rand($platforms, rand(1, 3));
                if (!is_array($selectedPlatforms)) {
                    $selectedPlatforms = [$selectedPlatforms];
                }
                $platformsArray = array_map(function($index) use ($platforms) {
                    return $platforms[$index];
                }, $selectedPlatforms);
                $title    = $this->titleForType($type, $blogTitles, $socialContent, $emailSubjects);

                $content = $this->contentForType($type, $title);
                $description = $this->excerpt($content);

                ContentPost::create([
                    'user_id'            => $user->id,
                    'client_id'          => null,
                    'category_id'        => $categories->random()->id,
                    'platform'          => $platformsArray,
                    'content_type'       => $type,
                    'title'              => $title,
                    'description'        => $description,
                    'content_url'        => null,
                    'post_count'         => rand(1, 5),
                    'scheduled_date'     => $status === 'scheduled' ? $publishDate : null,
                    'published_date'     => $status === 'published' ? $publishDate : null,
                    'status'             => $status,
                    'engagement_metrics'   => $this->generateEngagementMetrics($status),
                    'content_category'   => $type,
                    'tags'               => $this->generateTags($type, $categories->random()->name),
                    'notes'              => 'Generated by seeder',
                    'meta_description'   => $description,
                    'seo_keywords'       => $this->generateKeywords($type, $categories->random()->name),
                    'created_at'         => $createdDate,
                    'updated_at'         => $createdDate,
                ]);
            }
        }

        // 4. Seed featured posts
        $this->createFeaturedContent();
    }

    /* -----------------------------------------------------------------
     |  Helpers
     * ----------------------------------------------------------------- */

    private function titleForType(string $type, array $blog, array $social, array $email): string
    {
        switch ($type) {
            case 'blog':
                return $blog[array_rand($blog)];
            case 'social':
                return $social[array_rand($social)];
            case 'email':
                return $email[array_rand($email)];
            default:
                return 'Content Post: ' . ucfirst($type) . ' for Business Growth';
        }
    }

    private function contentForType(string $type, string $title): string
    {
        $templates = [
            'blog' => '<h2>Introduction</h2><p>In today\'s competitive business landscape, ' . $title . ' has become more important than ever. This comprehensive guide will walk you through proven strategies and actionable insights to help you achieve your business goals.</p><h2>Key Strategies</h2><ul><li>Start with a clear understanding of your target audience</li><li>Develop a comprehensive strategy based on data and insights</li><li>Implement consistent tracking and measurement systems</li><li>Continuously optimize based on performance data</li></ul><h2>Conclusion</h2><p>By following these strategies, you\'ll be well-positioned to achieve significant business growth and success.</p>',
            'social' => '<p>💡 Business Tip: ' . $title . '</p><p>Remember to stay focused on your goals! #BusinessGrowth #EntrepreneurLife</p>',
            'email' => '<p>Dear Valued Subscriber,</p><p>' . $title . '</p><p>We\'re excited to share this exclusive content with you. Here\'s what you\'ll discover:</p><ul><li>Proven strategies for business growth</li><li>Actionable insights you can implement today</li><li>Exclusive resources and tools</li></ul><p>Best regards,<br>The Team</p>',
            'video' => '<p>Welcome to our latest video content where we explore ' . $title . '. In this comprehensive guide, you\'ll learn:</p><ul><li>Step-by-step strategies for implementation</li><li>Real-world examples and case studies</li><li>Pro tips from industry experts</li></ul><p>Don\'t forget to like, comment, and subscribe for more valuable content!</p>',
            'podcast' => '<p>In this episode, we dive deep into ' . $title . '. Our expert guests share insights on:</p><ul><li>Latest industry trends and developments</li><li>Practical strategies for implementation</li><li>Common challenges and how to overcome them</li></ul><p>Listen now and transform your approach to business growth!</p>',
        ];

        return $templates[$type] ?? '<p>Content focused on ' . $title . ' to help drive business growth.</p>';
    }

    private function excerpt(string $content): string
    {
        return Str::limit(strip_tags($content), 150);
    }

    private function generateTags(string $type, string $category): array
    {
        $base = ['business', 'growth', 'strategy', 'marketing'];
        $map  = [
            'blog'        => ['article', 'guide', 'tips'],
            'social'      => ['social media', 'engagement'],
            'email'       => ['newsletter', 'subscriber'],
            'video'       => ['tutorial', 'visual'],
            'podcast'     => ['audio', 'interview'],
            'infographic' => ['data', 'visual'],
            'case_study'  => ['example', 'analysis'],
            'newsletter'  => ['update', 'news'],
        ];

        $tags = array_merge($base, $map[$type] ?? []);
        $tags[] = Str::slug($category);

        return $tags;
    }

    private function generateKeywords(string $type, string $category): string
    {
        $base = ['business', 'growth', 'strategy', 'marketing'];
        $map  = [
            'blog'        => ['article', 'guide', 'tips'],
            'social'      => ['social media', 'engagement'],
            'email'       => ['newsletter', 'subscriber'],
            'video'       => ['tutorial', 'visual'],
            'podcast'     => ['audio', 'interview'],
            'infographic' => ['data', 'visual'],
            'case_study'  => ['example', 'analysis'],
            'newsletter'  => ['update', 'news'],
        ];

        $keywords = array_merge($base, $map[$type] ?? []);
        $keywords[] = Str::slug($category);

        return implode(', ', $keywords);
    }

    private function generateEngagementMetrics(string $status): array
    {
        if ($status === 'published') {
            return [
                'views' => rand(100, 5000),
                'likes' => rand(10, 500),
                'comments' => rand(5, 100),
                'shares' => rand(5, 200),
            ];
        }

        return [];
    }

    private function createFeaturedContent(): void
    {
        $admin = User::where('role', 'admin')->first();
        $cat   = Category::firstOrCreate(['name' => 'Business Strategy']);

        $posts = [
            [
                'title' => 'The Ultimate Guide to Digital Marketing Success in 2024',
                'type'  => 'blog',
                'content' => 'Comprehensive guide covering SEO, social media, email marketing, and paid advertising.',
            ],
            [
                'title' => 'Client Success Story: How We Helped a Local Business Increase Revenue by 300%',
                'type'  => 'case_study',
                'content' => 'Detailed case study showcasing our proven methodology and results.',
            ],
        ];

        foreach ($posts as $p) {
            $content = $p['content'];
            $description = $this->excerpt($content);

            ContentPost::create([
                'user_id'          => $admin->id,
                'client_id'        => null,
                'category_id'      => $cat->id,
                'platform'        => ['website', 'facebook', 'linkedin'],
                'content_type'     => $p['type'],
                'title'            => $p['title'],
                'description'      => $description,
                'content_url'      => null,
                'post_count'       => 1,
                'published_date'   => now(),
                'status'           => 'published',
                'engagement_metrics' => [
                    'views' => rand(1500, 3000),
                    'likes' => rand(100, 300),
                    'comments' => rand(50, 150),
                    'shares' => rand(50, 150),
                ],
                'content_category' => $p['type'],
                'tags'             => ['featured', 'business', 'strategy', 'success'],
                'notes'            => 'Featured content generated by seeder',
                'meta_description' => $description,
                'seo_keywords'     => 'featured, business, strategy, success, digital marketing',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
