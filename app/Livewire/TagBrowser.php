<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Spatie\Tags\Tag;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TagBrowser extends Component
{
    use WithPagination;
    
    public $tagSlug;
    public $timeFilter = 'all';
    public $sortOrder = 'latest';
    public $isLoading = true;
    public $postsLoaded = false;
    
    protected $queryString = [
        'timeFilter' => ['except' => 'all', 'as' => 'time'],
        'sortOrder' => ['except' => 'latest', 'as' => 'sort'],
    ];
    
    public function mount($slug)
    {
        $this->tagSlug = $slug;
        $this->loadTag();
    }
    
    protected function loadTag(): void
    { 
        if (!Tag::where('slug->'.app()->getLocale(), $this->tagSlug)->orWhere('slug', $this->tagSlug)->exists()) {
             abort(404);
        }
    }
    
    protected function getTagObject(): Tag
    {
        return Tag::where('slug->'.app()->getLocale(), $this->tagSlug)
                  ->orWhere('slug', $this->tagSlug)
                  ->firstOrFail();
    }
    
    /**
     * Deferred loading of posts data
     * This method will be called by wire:init
     */
    public function loadPosts()
    {
        $this->postsLoaded = true;
        Log::info('TagBrowser: loadPosts() called, $postsLoaded = ' . ($this->postsLoaded ? 'true' : 'false'));
    }
    
    public function updatedTimeFilter()
    {
        $this->resetPage();
    }
    
    public function updatedSortOrder()
    {
        $this->resetPage();
    }
    
    // Computed Property untuk Posts
    public function getPostsProperty()
    {
        if (!$this->postsLoaded) { 
            Log::info('TagBrowser: getPostsProperty() called but postsLoaded is false');
            return collect();
        }

        $tag = $this->getTagObject();
        Log::info("TagBrowser: getPostsProperty() loading posts for tag: {$tag->name}");

        // Jangan gunakan cache dulu untuk debugging
        $query = Post::with(['media', 'category', 'tags'])
            ->withAnyTags([$tag->name]); 

        // Apply time filter
        switch ($this->timeFilter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', Carbon::now()->subMonth());
                break;
        }

        // Apply sorting
        switch ($this->sortOrder) {
            case 'latest':
                $query->latest();
                break;
            case 'views':
                $query->orderByDesc('views');
                break;
            case 'random':
                $query->inRandomOrder();
                break;
        }

        $posts = $query->paginate(16);
        Log::info("TagBrowser: Found {$posts->total()} posts for tag: {$tag->name}");
        
        return $posts;
    }
    
    // Computed Property untuk Stats
    public function getStatsProperty()
    {
        $tag = $this->getTagObject();
        $query = Post::withAnyTags([$tag->name]);
        switch ($this->timeFilter) {
            case 'today': $query->whereDate('created_at', Carbon::today()); break;
            case 'week': $query->where('created_at', '>=', Carbon::now()->subWeek()); break;
            case 'month': $query->where('created_at', '>=', Carbon::now()->subMonth()); break;
        }
        $totalPosts = $query->count();
        $totalViews = (clone $query)->sum('views'); 
        Log::info("TagBrowser: Stats for {$tag->name}: {$totalPosts} posts, {$totalViews} views");
        
        return [
            'totalPosts' => $totalPosts,
            'totalViews' => $totalViews
        ];
    }
    
    public function render(): View
    {
        $this->isLoading = false;
        $tag = $this->getTagObject();
        
        view()->share('title', '#' . $tag->name . ' - ' . config('app.name'));
        $tagDescription = 'Temukan koleksi video bokep dengan tag #' . $tag->name . ' hanya di Layar18. ';
        if(isset($this->stats['totalPosts'])){
            $tagDescription .= $this->stats['totalPosts'] . ' video tersedia.';
        }
        view()->share('meta_description', $tagDescription);
        
        // Debug info
        Log::info("TagBrowser: render() called for tag: {$tag->name}, postsLoaded: " . ($this->postsLoaded ? 'true' : 'false'));
        if ($this->postsLoaded) {
            Log::info("TagBrowser: posts count in render: " . $this->posts->count());
        }

        return view('livewire.tag-browser', [
            'posts' => $this->posts,
            'stats' => $this->stats,
            'tag' => $tag,
        ]);
    }
}
