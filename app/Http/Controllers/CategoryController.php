<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

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
        
        // Get other data for the view
        $totalPosts = Post::count();
        $totalViews = Post::sum('views');
        
        return view('categories.show', [
            'category' => $category,
            'posts' => $posts,
            'totalPosts' => $totalPosts,
            'totalViews' => $totalViews,
            'timeFilter' => $timeFilter,
            'sort' => $sort
        ]);
    }
}
