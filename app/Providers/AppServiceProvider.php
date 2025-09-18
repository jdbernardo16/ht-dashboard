<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\ContentPost;
use App\Models\Sale;
use App\Models\Task;
use App\Policies\ClientPolicy;
use App\Policies\ContentPostPolicy;
use App\Policies\SalePolicy;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);
        Gate::policy(ContentPost::class, ContentPostPolicy::class);

        Vite::prefetch(concurrency: 3);
    }
}
