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
        dd('Inside loadPosts() - wire:init triggered');
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
        // Only query the database once posts have been loaded
        // This helps with initial page load performance
        // if (!$this->postsLoaded) { // <-- Nonaktifkan cek ini SEMENTARA untuk memaksa query
        //    return collect();
        // }

        $cacheKey = "tag_posts_{$this->tag->id}_{$this->timeFilter}_{$this->sortOrder}_page{$this->page}"; // Akan error jika $this->tag null

        // return Cache::remember($cacheKey, now()->addMinutes(10), function () { // <-- Cache tetap NONAKTIF
            // KEMBALIKAN LOGIKA QUERY LENGKAP
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
                // case 'duration': // <-- Tetap dikomentari
                //    $query->orderByDesc('duration');
                //    break;
            }

            // DD SEBELUM PAGINATE
            $resultsBeforePaginate = (clone $query)->get(); // Clone agar tidak mengganggu paginate
            dd("Inside getPostsProperty - Before Paginate", 
               "Count:", $resultsBeforePaginate->count(), 
               "Results:", $resultsBeforePaginate, 
               "SQL:", $query->toSql(), 
               "Bindings:", $query->getBindings());

            return $query->paginate(16);
        // }); // <-- Cache tetap NONAKTIF
    }
    
    // Computed Property untuk Stats (Kembalikan ke normal, cache nonaktif)
    public function getStatsProperty()
    {
        $cacheKey = "tag_stats_{$this->tag->id}_{$this->timeFilter}";
        // return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $query = Post::withAnyTags([$this->tag->name]);
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
        // });
    }
    
    public function render(): View
    {
        $this->isLoading = false;
        
        // KEMBALIKAN RENDER KE NORMAL
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
