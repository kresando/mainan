<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Tags\Tag;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends BaseWidget
{
    // Mengoptimalkan interval polling untuk mengurangi beban server
    protected static ?string $pollingInterval = '30s';
    
    // Widget ini tidak perlu refresh otomatis saat form di halaman lain disubmit
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        // Menggunakan cache untuk mengurangi query database berulang
        // Cache selama 5 menit, cukup untuk dashboard admin
        $postCount = Cache::remember('stats.posts.count', 300, fn () => Post::count());
        $categoryCount = Cache::remember('stats.categories.count', 300, fn () => Category::count());
        $tagCount = Cache::remember('stats.tags.count', 300, fn () => Tag::count());

        return [
            Stat::make('Total Videos', $postCount)
                ->description('Total videos in database')
                ->descriptionIcon('heroicon-m-film')
                ->color('primary'),
                
            Stat::make('Categories', $categoryCount)
                ->description('Total categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('success'),
                
            Stat::make('Tags', $tagCount)
                ->description('Total tags')
                ->descriptionIcon('heroicon-m-hashtag')
                ->color('warning'),
        ];
    }
}
