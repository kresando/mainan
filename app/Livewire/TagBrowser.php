<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Spatie\Tags\Tag;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TagBrowser extends Component
{
    use WithPagination;
    
    public $tagSlug;
    public $timeFilter = 'all';
    public $sortOrder = 'latest';
    public $isLoading = true;
    public $postsLoaded = false;
    public $posts = [];
    
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
            return collect();
        }

        $tag = $this->getTagObject();

        $cacheKey = "tag_posts_{$tag->id}_{$this->timeFilter}_{$this->sortOrder}_page{$this->page}"; 

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
        }

        return $query->paginate(16);
    }
    
    // Computed Property untuk Stats (Kembalikan ke normal, cache nonaktif)
    public function getStatsProperty()
    {
        $tag = $this->getTagObject();
        $cacheKey = "tag_stats_{$tag->id}_{$this->timeFilter}";
        $query = Post::withAnyTags([$tag->name]);
        switch ($this->timeFilter) {
            case 'today': $query->whereDate('created_at', Carbon::today()); break;
            case 'week': $query->where('created_at', '>=', Carbon::now()->subWeek()); break;
            case 'month': $query->where('created_at', '>=', Carbon::now()->subMonth()); break;
        }
        $totalPosts = $query->count();
        $totalViews = (clone $query)->sum('views'); 
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

        return view('livewire.tag-browser', [
            'posts' => $this->posts,
            'stats' => $this->stats,
        ]);
    }
}
