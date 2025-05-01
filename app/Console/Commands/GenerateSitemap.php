<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Post;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap for the website.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemapPath = public_path('sitemap.xml');

        try {
            $sitemap = Sitemap::create();

            $homeRouteName = 'home';
            if (Route::has($homeRouteName)) {
                $sitemap->add(
                    Url::create(secure_url(route($homeRouteName)))
                       ->setLastModificationDate(Carbon::now())
                       ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                       ->setPriority(1.0)
                );
            } else {
                $sitemap->add(
                    Url::create(secure_url('/'))
                       ->setLastModificationDate(Carbon::now())
                       ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                       ->setPriority(1.0)
                );
                Log::warning("Sitemap Generator: Route '{$homeRouteName}' not found, using root '/' for homepage.");
            }

            $categories = Category::all();
            $categoryRouteName = 'categories.show';
            if (!Route::has($categoryRouteName)) {
                 Log::warning("Sitemap Generator: Route '{$categoryRouteName}' not found. Skipping category URLs.");
            } else {
                foreach ($categories as $category) {
                     $sitemap->add(
                        Url::create(secure_url(route($categoryRouteName, $category->slug)))
                           ->setLastModificationDate($category->updated_at ?? Carbon::now())
                           ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                           ->setPriority(0.9)
                    );
                }
            }

            $posts = Post::with(['category', 'media' => function ($query) {
                $query->where('collection_name', 'thumbnail');
            }])->get();
            $this->info("Total posts retrieved: " . $posts->count());

            $postRouteName = 'posts.show';
            if (!Route::has($postRouteName)) {
                 Log::error("Sitemap Generator: Fatal Error - Route '{$postRouteName}' not found. Cannot generate post URLs.");
                 $this->error("Route '{$postRouteName}' is required but not found. Aborting post URL generation.");
            } else {
                $postCounter = 0;
                foreach ($posts as $post) {
                    if (!($post instanceof HasMedia)) {
                         Log::warning("Sitemap Generator: Post ID {$post->id} does not implement HasMedia. Skipping media checks.");
                         continue;
                    }
                
                    $postCounter++;

                    $postUrl = secure_url(route($postRouteName, $post->slug));

                    /** @var Media|null $thumbnailMedia */
                    $thumbnailMedia = $post->getFirstMedia('thumbnail');
                    $thumbnailUrl = $thumbnailMedia?->getFullUrl();

                    if ($postCounter <= 5) {
                        $this->info("-- Processing Post ID: {$post->id} --");
                        $this->info("   Embed Link Raw: " . ($post->embed_link ?? 'NULL'));
                        $this->info("   Thumbnail Media Found: " . ($thumbnailMedia ? 'YES' : 'NO'));
                        $this->info("   Thumbnail Final URL (from MediaLib): " . ($thumbnailUrl ?? 'NULL'));
                    }

                    $urlEntry = Url::create($postUrl)
                                   ->setLastModificationDate($post->updated_at ?? Carbon::now())
                                   ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                                   ->setPriority(0.8);

                    if ($thumbnailUrl) {
                        $caption = $thumbnailMedia->getCustomProperty('caption', $post->title ?? 'Gambar Post');
                        $urlEntry->addImage($thumbnailUrl, $caption);
                    }

                    $shouldAddVideo = false;
                    if (!empty($post->embed_link) && $thumbnailUrl) {
                        $shouldAddVideo = true;
                        $urlEntry->addVideo(
                            $thumbnailUrl,
                            $post->title ?? 'Video Post',
                            $post->description ?? 'Deskripsi video',
                            null,
                            $postUrl,
                            [
                                'publication_date' => ($post->created_at ?? Carbon::now())->toW3cString(),
                                'family_friendly' => 'no',
                                'requires_subscription' => 'no',
                                'live' => 'no',
                                'category' => $post->category?->name,
                            ]
                        );
                    } elseif (!empty($post->embed_link) && !$thumbnailUrl) {
                         Log::warning("Sitemap Generator: Post ID {$post->id} has embed link but no thumbnail media found in 'thumbnail' collection. Video details skipped.");
                    }

                    if ($postCounter <= 5) {
                        $this->info("   Should add video details? " . ($shouldAddVideo ? 'YES' : 'NO'));
                    }

                    $sitemap->add($urlEntry);
                }
            }

            $sitemap->writeToFile($sitemapPath);

            $this->info("Sitemap generated successfully at: {$sitemapPath}");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            Log::error("Failed to generate sitemap: " . $e->getMessage(), ['exception' => $e]);
            $this->error("Failed to generate sitemap. Check logs for details.");
            return Command::FAILURE;
        }
    }
}
