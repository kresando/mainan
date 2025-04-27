<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class TrackPostViews
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Make sure we're on a post detail page and get the post
        if ($request->route('post') instanceof Post) {
            $post = $request->route('post');
            $cookieName = 'post_viewed_' . $post->id;
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            
            // Create a unique key based on post ID, IP address, and a simplified user agent
            $viewKey = 'post_view_' . $post->id . '_' . md5($ipAddress . $userAgent);
            
            // If no cookie exists and view is not cached, increment the view count
            if (!Cookie::has($cookieName) && !Cache::has($viewKey)) {
                // Increment view count with a DB query only once
                $post->increment('views');
                
                // Set a cookie for 24 hours to prevent multiple views
                Cookie::queue($cookieName, '1', 60 * 24);
                
                // Also cache this view in the database cache for additional protection
                Cache::put($viewKey, true, now()->addHours(24));
            }
        }
        
        return $response;
    }
}
