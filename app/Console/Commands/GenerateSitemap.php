<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\Tags\Video;
use App\Models\Post;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

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

            $posts = Post::with(['category'])->get();
            $postRouteName = 'posts.show';
            if (!Route::has($postRouteName)) {
                 Log::error("Sitemap Generator: Fatal Error - Route '{$postRouteName}' not found. Cannot generate post URLs.");
                 $this->error("Route '{$postRouteName}' is required but not found. Aborting post URL generation.");
            } else {
                foreach ($posts as $post) {
                    $postUrl = secure_url(route($postRouteName, $post->slug));

                    $thumbnailUrl = null;
                    if (!empty($post->thumbnail)) {
                        $thumbnailUrl = filter_var($post->thumbnail, FILTER_VALIDATE_URL)
                                        ? $post->thumbnail
                                        : secure_asset($post->thumbnail);
                    }

                    $urlEntry = Url::create($postUrl)
                                   ->setLastModificationDate($post->updated_at ?? Carbon::now())
                                   ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                                   ->setPriority(0.8);

                    if ($thumbnailUrl) {
                        $urlEntry->addImage($thumbnailUrl, $post->title ?? 'Gambar Post');
                    }

                    if (!empty($post->link_embed) && $thumbnailUrl) {
                        $urlEntry->addVideo(
                            $thumbnailUrl,
                            $post->title ?? 'Video Post',
                            $post->description ?? 'Deskripsi video',
                            null,
                            $postUrl,
                            [
                                'publication_date' => ($post->created_at ?? Carbon::now())->toW3cString(),
                                'family_friendly' => false,
                                'requires_subscription' => false,
                                'live' => false,
                                'category' => $post->category?->name,
                            ]
                        );
                    } elseif (!empty($post->link_embed) && !$thumbnailUrl) {
                        Log::warning("Sitemap Generator: Post ID {$post->id} has embed link but no thumbnail. Video details skipped for sitemap.");
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
