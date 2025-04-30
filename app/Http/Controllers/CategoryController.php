<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display all posts from a specific category with filtering and sorting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $slug = null)
    {
        $timeFilter = $request->input('filter', 'all');
        $sort = $request->input('sort', 'latest');
        
        // Get category by slug or from request
        $category = null;
        if ($slug) {
            $category = Category::where('slug', $slug)->firstOrFail();
        } elseif ($request->has('category')) {
            $category = Category::where('slug', $request->input('category'))->firstOrFail();
        } else {
            abort(404);
        }
        
        // Build query with eager loading for media and category
        $query = Post::with(['media', 'category'])->where('category_id', $category->id);
        
        // Apply time filter
        switch ($timeFilter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
                break;
            case 'month':
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
                break;
            default:
                // All time - no filter
                break;
        }
        
        // Apply sorting
        switch ($sort) {
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            case 'random':
                $query->inRandomOrder();
                break;
            default:
                $query->latest();
                break;
        }
        
        // Get posts with pagination
        $posts = $query->paginate(16)->withQueryString();
        
        // Get specific category stats with caching
        $categoryStats = Cache::remember("category_stats_{$category->id}", now()->addMinutes(30), function () use ($category) {
            // Assuming Category model has a 'posts' relationship
            $postQuery = $category->posts(); 
            return [
                'totalPosts' => $postQuery->count(),
                'totalViews' => $postQuery->sum('views')
            ];
        });
        
        // -- Persiapan SEO --
        $title = 'Kumpulan Video ' . $category->name . ' Terbaru - Layar18';
        $description = 'Nonton dan streaming kumpulan video bokep kategori ' . $category->name . ' terbaru dan terpopuler di Layar18. ' . $categoryStats['totalPosts'] . ' video tersedia.';
        // ----
        
        // Pass data SEO ke view
        return view('categories.show', [
            'category' => $category,
            'posts' => $posts, // Paginator object
            'totalPosts' => $categoryStats['totalPosts'], // Specific category total posts
            'totalViews' => $categoryStats['totalViews'], // Specific category total views
            'timeFilter' => $timeFilter,
            'sort' => $sort,
            'title' => $title, // Pass SEO title
            'description' => $description // Pass SEO description
        ]);
    }
}
