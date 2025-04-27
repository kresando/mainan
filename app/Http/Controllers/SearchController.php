<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Display search results.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $query = $request->input('q');
        
        $posts = collect([]);
        $totalPosts = 0;
        $totalViews = 0;
        
        if ($query && strlen($query) >= 2) {
            $posts = Post::with(['media', 'category'])
                ->where('title', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%')
                ->latest()
                ->paginate(24)
                ->withQueryString();
                
            $totalPosts = Post::count();
            $totalViews = Post::sum('views');
        }
        
        return view('search', [
            'query' => $query,
            'posts' => $posts,
            'totalPosts' => $totalPosts,
            'totalViews' => $totalViews
        ]);
    }
} 