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
        'thumbnail',
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
    
    // Method untuk Filament FileUpload
    public function getThumbnailAttribute($value)
    {
        // Cek jika value sudah ada sebagai URL atau path
        if ($value) {
            // Jika sudah valid URL, gunakan langsung
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }
            
            // Jika path relatif, tambahkan storage URL
            if (is_string($value) && !str_starts_with($value, 'http')) {
                return asset('storage/' . $value);
            }
        }
        
        // Jika nilai NULL atau tidak valid, coba dapatkan dari Spatie Media
        $mediaUrl = $this->getFirstMediaUrl('thumbnail');
        if ($mediaUrl) {
            return $mediaUrl;
        }
        
        // Jika semua gagal, kembalikan URL data SVG
        return "data:image/svg+xml;base64,".base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 16 9" fill="none"><rect width="16" height="9" fill="#374151"/><path d="M7 4.5C7 4.77614 6.77614 5 6.5 5C6.22386 5 6 4.77614 6 4.5C6 4.22386 6.22386 4 6.5 4C6.77614 4 7 4.22386 7 4.5Z" fill="white"/><path d="M10 4.5C10 4.77614 9.77614 5 9.5 5C9.22386 5 9 4.77614 9 4.5C9 4.22386 9.22386 4 9.5 4C9.77614 4 10 4.22386 10 4.5Z" fill="white"/><path d="M8 7C6.5 7 6 6 6 6H10C10 6 9.5 7 8 7Z" fill="white"/></svg>');
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
