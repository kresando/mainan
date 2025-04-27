<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Tags\Tag;

class TagController extends Controller
{
    /**
     * Display posts with the specified tag.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, ?string $slug = null): View|RedirectResponse
    {
        // If no slug provided, try to get it from request
        if (!$slug && $request->has('tag')) {
            $slug = $request->input('tag');
        }

        // If still no slug, redirect to home
        if (!$slug) {
            return redirect()->route('home');
        }

        // Get the tag by slug
        $tag = Tag::where('slug->en', $slug)->first();
        
        if (!$tag) {
            abort(404);
        }

        // Get time filter from request
        $timeFilter = $request->input('time', 'all');
        
        // Get sort order from request
        $sortOrder = $request->input('sort', 'latest');
        
        // Start building the query with eager loading
        $query = Post::with(['media', 'category'])->withAllTags([$tag->name]);
        
        // Apply time filter
        switch($timeFilter) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->where('created_at', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', now()->subMonth());
                break;
        }
        
        // Apply sorting
        switch($sortOrder) {
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            case 'duration':
                $query->orderBy('duration', 'desc');
                break;
            default:
                $query->latest();
                break;
        }
        
        // Get posts with pagination
        $posts = $query->paginate(24)->withQueryString();
        
        // Get total posts and views for this tag
        $totalPosts = Post::withAllTags([$tag->name])->count();
        $totalViews = Post::withAllTags([$tag->name])->sum('views');
        
        return view('tags.show', [
            'tag' => $tag,
            'posts' => $posts,
            'totalPosts' => $totalPosts,
            'totalViews' => $totalViews,
            'timeFilter' => $timeFilter,
            'sortOrder' => $sortOrder
        ]);
    }
} 