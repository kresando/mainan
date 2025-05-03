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
    
    public $tag;
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
    
    protected function loadTag()
    {
        $this->tag = Tag::where('slug->'.app()->getLocale(), $this->tagSlug)
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
    
    public function getPostsProperty()
    {
        // Only query the database once posts have been loaded
        // This helps with initial page load performance
        if (!$this->postsLoaded) {
            return collect();
        }
        
        // Use cache to avoid repeating the same expensive queries
        $cacheKey = "tag_posts_{$this->tag->id}_{$this->timeFilter}_{$this->sortOrder}_page{$this->page}";
        
        return Cache::remember($cacheKey, now()->addMinutes(10), function () {
            $query = Post::with(['media', 'category', 'tags'])
                ->withAnyTags([$this->tag->name]);
            
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
                // case 'duration': // <-- Komentari atau hapus jika kolom 'duration' tidak ada di tabel 'posts'
                //    $query->orderByDesc('duration');
                //    break;
            }
            
            return $query->paginate(16);
        });
    }
    
    public function getStatsProperty()
    {
        $cacheKey = "tag_stats_{$this->tag->id}_{$this->timeFilter}";
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $query = Post::withAnyTags([$this->tag->name]);
            
            // Apply time filter for stats too
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
            
            $totalPosts = $query->count();
            $totalViews = $query->sum('views');
            
            return [
                'totalPosts' => $totalPosts,
                'totalViews' => $totalViews
            ];
        });
    }
    
    public function render(): View
    {
        $this->isLoading = false;
        
        // Set the page title and meta description
        view()->share('title', '#' . $this->tag->name . ' - ' . config('app.name'));
        $tagDescription = 'Temukan koleksi video bokep dengan tag #' . $this->tag->name . ' hanya di Layar18. ';
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
