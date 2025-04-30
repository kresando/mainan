<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Spatie\Tags\Tag;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Menampilkan detail post.
     * 
     * @param \App\Models\Post $post
     * @return \Illuminate\View\View
     */
    public function show(Post $post)
    {
        // Load tags jika belum di-load
        if (!$post->relationLoaded('tags')) {
            $post->load('tags');
        }
        
        // Increment views
        $post->increment('views');
        
        // -- Persiapan SEO --
        $title = $post->title . ' - Layar18';
        $description = 'Nonton video ' . $post->title . '. ' . Str::limit(strip_tags($post->description), 120);
        $ogImageUrl = $post->getFirstMediaUrl('thumbnail');
        // ----
        
        // Get related posts (same category and at least one same tag)
        $relatedPosts = Post::with(['media', 'category', 'tags'])
            ->where('id', '!=', $post->id)
            ->where(function ($query) use ($post) {
                // Post dengan kategori yang sama
                $query->where('category_id', $post->category_id);
                
                // Jika tidak ada kategori, ambil semua post
                if (!$post->category_id) {
                    $query->orWhereNotNull('id');
                }
            })
            ->when($post->tags->isNotEmpty(), function ($query) use ($post) {
                // Get current post tag names
                $tagNames = $post->tags->pluck('name')->toArray();
                
                // Match posts with at least one of the same tags, tapi tidak diharuskan
                return $query->withAnyTags($tagNames);
            })
            ->latest()
            ->take(6)
            ->get();
        
        // Jika related posts kurang dari 3, tambahkan post random sebagai backup
        if ($relatedPosts->count() < 3) {
            // Ambil post lain secara random, yang belum ada di related posts dan bukan post saat ini
            $additionalPosts = Post::with(['media', 'category', 'tags'])
                ->where('id', '!=', $post->id)
                ->whereNotIn('id', $relatedPosts->pluck('id')->toArray())
                ->inRandomOrder()
                ->take(6 - $relatedPosts->count())
                ->get();
            
            // Gabungkan dengan related posts yang sudah ada
            $relatedPosts = $relatedPosts->concat($additionalPosts);
        }
            
        // Pass data SEO ke view
        return view('posts.show', compact('post', 'relatedPosts', 'title', 'description', 'ogImageUrl'));
    }
}
