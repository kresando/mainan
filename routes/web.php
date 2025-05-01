<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\SitemapController;
use App\Livewire\TagBrowser;
use Illuminate\Support\Facades\Route;
use Spatie\Tags\Tag;

Route::get('/', WelcomeController::class)->name('home');

// Route untuk post detail dengan middleware track.views
Route::get('/posts/{post}', [PostController::class, 'show'])
    ->middleware('track.views')
    ->name('posts.show');

// Route for showing all posts in a category
Route::get('/category/{slug?}', [CategoryController::class, 'show'])->name('categories.show');

// Search route
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Tag route
Route::get('/tag/{slug?}', function($slug = null) {
    if (!$slug) {
        // Handle case where no slug is provided (e.g., redirect or show all tags page)
        abort(404); // Or redirect, or return a different view
    }
    
    // Find the tag by slug (assuming locale-based slug)
    $tag = Tag::where('slug->'.app()->getLocale(), $slug)->orWhere('slug', $slug)->firstOrFail();
    
    // Return the view, passing both slug and the tag object
    return view('tags.show', ['slug' => $slug, 'tag' => $tag]); 

})->name('tags.show');

// Sitemap route
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
