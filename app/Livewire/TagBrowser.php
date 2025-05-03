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
    
    public function getPostsProperty()
    {
        // Only query the database once posts have been loaded
        // This helps with initial page load performance
        if (!$this->postsLoaded) {
            return collect();
        }
        
        $cacheKey = "tag_posts_{$this->tag->id}_{$this->timeFilter}_{$this->sortOrder}_page{$this->page}";
        
        // return Cache::remember($cacheKey, now()->addMinutes(10), function () { // <-- NONAKTIFKAN CACHE
            // Query SANGAT disederhanakan untuk debug
            $query = Post::withAnyTags([$this->tag->name]); 

            // dd() SEGERA setelah query dasar
            // dd("Hasil query dasar (withAnyTags saja):", $query->get()); // <-- DD SEBELUMNYA DIKOMENTARI

            /* BAGIAN FILTER/SORT/EAGER LOADING YANG DIKOMENTARI SEMENTARA
            $query = Post::with(['media', 'category', 'tags'])
                ->withAnyTags([$this->tag->name]); // Akan error jika $this->tag null

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

            // DEBUG: Lihat jumlah sebelum paginate dan data mentahnya
            // $countBeforePaginate = $query->count();
            // $rawData = $query->limit(5)->get(); // Ambil beberapa data mentah untuk dilihat
            // dump("Jumlah post sebelum paginate:", $countBeforePaginate);
            // dump("Data mentah (limit 5):", $rawData);
            // --- AKHIR DEBUG ---
            */

            // Kembalikan query SEMENTARA untuk menghindari error saat dd() utama dihilangkan
            return $query->paginate(16); 
        // }); // <-- NONAKTIFKAN CACHE
    }
    
    public function getStatsProperty()
    {
        dd('Inside getStatsProperty - START'); // <-- DD DI AWAL METHOD

        $cacheKey = "tag_stats_{$this->tag->id}_{$this->timeFilter}"; // Akan error jika $this->tag null

        // return Cache::remember($cacheKey, now()->addMinutes(30), function () { // <-- NONAKTIFKAN CACHE STATS
            $query = Post::withAnyTags([$this->tag->name]); // Akan error jika $this->tag null

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
            
            // DEBUG STATS
            try {
                $totalPosts = $query->count();
                // Clone query sebelum sum agar count tidak terpengaruh
                $totalViews = (clone $query)->sum('views'); 
                dump("Stats Query Results:", ['totalPosts' => $totalPosts, 'totalViews' => $totalViews]);
            } catch (\Exception $e) {
                dump("Error executing stats query:", $e->getMessage());
                $totalPosts = 0;
                $totalViews = 0;
            }
            // AKHIR DEBUG STATS

            return [
                'totalPosts' => $totalPosts,
                'totalViews' => $totalViews
            ];
        // }); // <-- NONAKTIFKAN CACHE STATS
    }
    
    public function render(): View
    {
        $this->isLoading = false;
        
        // dd('Inside render() - before returning view'); // <-- HAPUS DD DARI SINI
        
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
