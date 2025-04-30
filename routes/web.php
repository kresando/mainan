<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\SitemapController;
use App\Livewire\TagBrowser;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');

// Route untuk post detail dengan middleware track.views
Route::get('/posts/{post}', [PostController::class, 'show'])
    ->middleware('track.views')
    ->name('posts.show');

// Route for showing all posts in a category
Route::get('/category/{slug?}', [CategoryController::class, 'show'])->name('categories.show');

// Search route
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Tag route - using TagController
Route::get('/tag/{slug?}', function($slug = null) {
    return view('tags.show', ['slug' => $slug]);
})->name('tags.show');

// Sitemap route
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
