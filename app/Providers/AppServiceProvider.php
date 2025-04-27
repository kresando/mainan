<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
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
        // Enable strict mode in development for better query debugging
        if ($this->app->environment('local')) {
            Model::shouldBeStrict();
        }
        
        // Cache common queries that don't change frequently
        $this->cacheCommonQueries();
    }
    
    /**
     * Cache commonly used queries to reduce database load
     */
    protected function cacheCommonQueries(): void
    {
        // Cache categories for 24 hours - these don't change often
        if (!Cache::has('all_categories')) {
            Cache::remember('all_categories', 60 * 24, function () {
                return Category::all();
            });
        }
        
        // Cache popular posts for 6 hours
        if (!Cache::has('popular_posts')) {
            Cache::remember('popular_posts', 60 * 6, function () {
                return Post::with('category')
                    ->orderBy('views', 'desc')
                    ->take(5)
                    ->get();
            });
        }
    }
}
