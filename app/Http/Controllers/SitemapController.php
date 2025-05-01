<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Post;
use App\Models\Category;
use Spatie\Tags\Tag; // Gunakan model Tag dari Spatie
use Carbon\Carbon;

class SitemapController extends Controller
{
    public function index(Request $request)
    {
        $sitemap = Sitemap::create();

        // 1. Homepage
        $sitemap->add(Url::create(route('home'))
            ->setLastModificationDate(Carbon::now()) // Bisa diganti dengan waktu update terakhir jika ada
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(1.0));

        // 2. Posts
        Post::with('media') // Eager load media
              ->orderBy('created_at', 'desc')
              ->chunk(200, function ($posts) use ($sitemap) { // Chunking untuk performa
            foreach ($posts as $post) {
                $url = Url::create(route('posts.show', $post))
                    ->setLastModificationDate($post->updated_at ?? $post->created_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY) // Atau daily jika sering update
                    ->setPriority(0.8);
                    
                // Tambahkan gambar thumbnail jika ada
                $thumbnailUrl = $post->getFirstMediaUrl('thumbnail');
                if ($thumbnailUrl) {
                    $url->addImage($thumbnailUrl, $post->title); // Title gambar opsional
                }
                
                $sitemap->add($url);
            }
        });
        
        // 3. Categories
        Category::chunk(200, function ($categories) use ($sitemap) {
            foreach ($categories as $category) {
                $sitemap->add(Url::create(route('categories.show', $category))
                    // ->setLastModificationDate() // Bisa ditambahkan jika kategori punya updated_at
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.9));
            }
        });

        // 4. Tags
        Tag::chunk(200, function ($tags) use ($sitemap) {
            foreach ($tags as $tag) {
                 // Ambil slug dari locale default atau yang pertama tersedia jika perlu
                 $locale = config('app.locale', 'en'); // Tentukan locale
                 $slug = $tag->getTranslation('slug', $locale); 
                 
                 // Fallback jika slug untuk locale default tidak ada (misal hanya ada 'en')
                 if (!$slug && $locale !== 'en') {
                     $slug = $tag->getTranslation('slug', 'en');
                 }
                 
                 if ($slug) { // Pastikan slug ada
                    $sitemap->add(Url::create(route('tags.show', $slug))
                        // ->setLastModificationDate() // Bisa ditambahkan jika tag punya updated_at
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.7));
                 }
            }
        });

        // Generate sitemap XML response
        return $sitemap->toResponse($request);
    }
}
