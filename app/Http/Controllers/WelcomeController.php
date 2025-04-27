<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Find the Bokep Indo category
        $indoCategory = Category::where('name', 'Bokep Indo')->orWhere('slug', 'bokep-indo')->first();
        
        // Find the Bokep JAV category
        $javCategory = Category::where('name', 'Bokep JAV')->orWhere('slug', 'bokep-jav')->first();
        
        // If Indo category exists, get posts from that category
        if ($indoCategory) {
            $indoPosts = Post::with('category')
                ->where('category_id', $indoCategory->id)
                ->latest()
                ->take(8)
                ->get();
        } else {
            // Fallback to empty collection if category doesn't exist
            $indoPosts = collect([]);
        }
        
        // If JAV category exists, get posts from that category
        if ($javCategory) {
            $javPosts = Post::with('category')
                ->where('category_id', $javCategory->id)
                ->latest()
                ->take(8)
                ->get();
        } else {
            // Fallback to empty collection if category doesn't exist
            $javPosts = collect([]);
        }
            
        return view('welcome', compact('indoPosts', 'javPosts'));
    }
} 