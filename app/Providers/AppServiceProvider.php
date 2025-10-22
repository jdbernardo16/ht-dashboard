<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\ContentPost;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\Sale;
use App\Models\Task;
use App\Observers\ContentPostObserver;
use App\Observers\ExpenseObserver;
use App\Observers\GoalObserver;
use App\Observers\SaleObserver;
use App\Observers\TaskObserver;
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

        // Register model observers
        Task::observe(TaskObserver::class);
        Sale::observe(SaleObserver::class);
        Expense::observe(ExpenseObserver::class);
        Goal::observe(GoalObserver::class);
        ContentPost::observe(ContentPostObserver::class);

        Vite::prefetch(concurrency: 3);
    }
}
