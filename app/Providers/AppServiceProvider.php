<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException; // Import QueryException
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log; // Import Log facade jika ingin log warning
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
// use Illuminate\Support\Facades\Schema; // Uncomment jika Anda ingin menggunakan cek Schema::hasTable

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
        // Enable strict mode only in local environment AND not during console commands
        if ($this->app->environment('local') && !$this->app->runningInConsole()) {
            Model::shouldBeStrict();
        }

        // *** PERUBAHAN UTAMA: Kondisi Pengecekan Console ***
        // Hanya jalankan cache common queries jika aplikasi TIDAK berjalan di console.
        // Ini mencegah query DB/Cache saat artisan (misalnya package:discover) dijalankan selama build.
        if (!$this->app->runningInConsole()) {
            // Tambahkan try-catch untuk menangani error jika DB belum siap saat boot normal (runtime)
            try {
                $this->cacheCommonQueries();
            } catch (QueryException $e) {
                // Tangani secara spesifik jika DB belum siap saat boot normal.
                // Anda bisa log warning agar tahu jika ini terjadi di production.
                Log::warning('AppServiceProvider: Database query failed during boot, likely DB not ready yet. Skipping cache warm-up. Error: ' . $e->getMessage());
                // Jangan re-throw error agar aplikasi tetap bisa boot jika memungkinkan.
            } catch (\Exception $e) {
                // Tangani error umum lainnya saat boot normal.
                Log::error('AppServiceProvider: Unexpected exception during boot while caching common queries: ' . $e->getMessage());
            }
        }
    }

    /**
     * Cache commonly used queries to reduce database load.
     * Metode ini dipanggil dari boot() HANYA jika tidak berjalan di console.
     */
    protected function cacheCommonQueries(): void
    {
        // Cache categories - Gunakan durasi Cache::remember yang lebih modern
        // Hapus pengecekan `if (!Cache::has(...))` karena `remember` sudah melakukannya.
        Cache::remember('all_categories', now()->addHours(24), function () {
            Log::info('AppServiceProvider: Caching all_categories.'); // Tambahkan log jika ingin debug
            return Category::all();
        });

        // Cache popular posts - Gunakan durasi Cache::remember yang lebih modern
        Cache::remember('popular_posts', now()->addHours(6), function () {
            Log::info('AppServiceProvider: Caching popular_posts.'); // Tambahkan log jika ingin debug
            return Post::with('category')
                ->orderBy('views', 'desc')
                ->take(5)
                ->get();
        });
    }
}