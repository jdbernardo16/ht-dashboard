<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Business Strategy',
                'slug' => 'business-strategy',
                'description' => 'Content related to business planning and strategic initiatives',
                'color' => '#3b82f6',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Marketing campaigns, social media, and promotional content',
                'color' => '#10b981',
                'is_active' => true,
            ],
            [
                'name' => 'Sales',
                'slug' => 'sales',
                'description' => 'Sales strategies, client communications, and revenue generation',
                'color' => '#f59e0b',
                'is_active' => true,
            ],
            [
                'name' => 'Operations',
                'slug' => 'operations',
                'description' => 'Daily operations, process improvements, and workflow optimization',
                'color' => '#8b5cf6',
                'is_active' => true,
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'description' => 'Financial planning, budgeting, and expense management',
                'color' => '#ef4444',
                'is_active' => true,
            ],
            [
                'name' => 'Product Development',
                'slug' => 'product-development',
                'description' => 'Product creation, feature development, and innovation',
                'color' => '#06b6d4',
                'is_active' => true,
            ],
            [
                'name' => 'Customer Service',
                'slug' => 'customer-service',
                'description' => 'Client support, feedback management, and service improvements',
                'color' => '#84cc16',
                'is_active' => true,
            ],
            [
                'name' => 'Team Management',
                'slug' => 'team-management',
                'description' => 'Team building, leadership, and human resources',
                'color' => '#f97316',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
