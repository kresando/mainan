<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

class Post extends Model implements HasMedia
{
    use HasFactory;
    use HasTags;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'embed_link',
        'description',
        'views',
    ];
    
    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['category'];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'views' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')
            ->singleFile();
    }
    
    // Method untuk format jumlah views agar lebih mudah dibaca
    public function getFormattedViewsAttribute()
    {
        if ($this->views > 1000000) {
            return round($this->views / 1000000, 1) . 'M';
        }
        if ($this->views > 1000) {
            return round($this->views / 1000, 1) . 'K';
        }
        return $this->views;
    }
    
    /**
     * Scope a query to get popular posts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePopular(Builder $query, int $limit = 5): Builder
    {
        return $query->orderBy('views', 'desc')->take($limit);
    }
    
    /**
     * Get popular posts with caching.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPopular(int $limit = 5)
    {
        $cacheKey = 'popular_posts_' . $limit;
        
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($limit) {
            return static::with('category')->popular($limit)->get();
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (!$post->slug) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && !$post->isDirty('slug')) {
                $post->slug = Str::slug($post->title);
            }
        });
        
        // Clear cached queries when a post is updated or created
        static::saved(function () {
            Cache::forget('popular_posts');
            // Clear any cached queries that might contain posts
            foreach (range(1, 10) as $count) {
                Cache::forget('popular_posts_' . $count);
            }
        });
    }
}
