@extends('layouts.app')

{{-- SEO Title --}}
@section('title', $title)

{{-- SEO Meta Description & OG/Twitter Overrides --}}
@section('meta_description', $description)

@section('meta')
    {{-- Default Meta (termasuk canonical) dari app.blade.php akan di-render di sini --}}
    @parent 
    
    {{-- Open Graph Overrides --}}
    <meta property="og:type" content="video.other" />
    <meta property="og:title" content="{{ $title }}" />
    <meta property="og:description" content="{{ $description }}" />
    @if($ogImageUrl)
    <meta property="og:image" content="{{ $ogImageUrl }}" />
    @endif
    
    {{-- Twitter Card Overrides --}}
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $title }}" />
    <meta name="twitter:description" content="{{ $description }}" />
    @if($ogImageUrl)
    <meta name="twitter:image" content="{{ $ogImageUrl }}" />
    @endif
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb Navigation -->
    <nav class="flex mb-6 text-sm text-gray-500 dark:text-gray-400">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center hover:text-blue-600 dark:hover:text-blue-400" wire:navigate>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 mx-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="{{ route('categories.show', $post->category->slug) }}" class="ml-1 hover:text-blue-600 dark:hover:text-blue-400" wire:navigate>
                        {{ $post->category->name }}
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 mx-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="ml-1 font-medium truncate max-w-xs">{{ $post->title }}</span>
                </div>
            </li>
        </ol>
    </nav>
    
    <!-- Main Content -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden mb-8">
        <!-- Video Embed -->
        <div class="relative pt-[56.25%] bg-black">
            <iframe class="absolute inset-0 w-full h-full" 
                src="{{ $post->embed_link }}" 
                title="{{ $post->title }}"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen></iframe>
        </div>
        
        <!-- Post Content -->
        <div class="p-6">
            <!-- Post Title -->
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4 leading-tight">{{ $post->title }}</h1>
            
            <!-- Description -->
            @if($post->description)
            <div class="prose prose-lg dark:prose-invert max-w-none mb-6 text-gray-800 dark:text-gray-200">
                {!! $post->description !!}
            </div>
            @endif
            
            <!-- Meta Information -->
            <div class="border-t border-gray-200 dark:border-zinc-700 pt-6 mt-6">
                <div class="flex flex-wrap items-center gap-4 mb-6">
                    <!-- Category -->
                    @if($post->category)
                    <a href="{{ route('categories.show', $post->category->slug) }}" class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200" wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        {{ $post->category->name }}
                    </a>
                    @endif
                    
                    <!-- Views Count -->
                    <div class="inline-flex items-center text-gray-600 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span>{{ number_format($post->views) }} views</span>
                    </div>
                    
                    <!-- Publication Date -->
                    <div class="inline-flex items-center text-gray-600 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                
                <!-- Tags -->
                @if($post->tags && $post->tags->count() > 0)
                <div class="mt-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($post->tags as $tag)
                        <a href="{{ route('tags.show', $tag->slug) }}" class="px-3 py-1 bg-gray-100 dark:bg-zinc-700 hover:bg-gray-200 dark:hover:bg-zinc-600 rounded-full text-sm text-gray-800 dark:text-gray-200 transition-colors" wire:navigate>
                            #{{ $tag->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Related Posts -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Related Videos</h2>
        
        @if($relatedPosts->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($relatedPosts as $relatedPost)
                <x-post-card :post="$relatedPost" />
            @endforeach
        </div>
        @else
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6 text-center">
            <p class="text-gray-600 dark:text-gray-400">Tidak ada video terkait saat ini.</p>
        </div>
        @endif
    </div>
</div>

{{-- Schema.org JSON-LD for VideoObject --}}
@php
    // Pastikan $ogImageUrl ada dan valid URL
    $schemaThumbnailUrl = filter_var($ogImageUrl, FILTER_VALIDATE_URL) ? $ogImageUrl : null;
@endphp
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "{{ addslashes($post->title) }}",
  "description": "{{ addslashes(Str::limit(strip_tags($post->description), 250)) }}",
  @if($schemaThumbnailUrl)
  "thumbnailUrl": "{{ $schemaThumbnailUrl }}",
  @endif
  "uploadDate": "{{ $post->created_at->toIso8601String() }}",
  "embedUrl": "{{ $post->embed_link }}",
  "interactionStatistic": [
    {
      "@type": "InteractionCounter",
      "interactionType": { "@type": "WatchAction" },
      "userInteractionCount": {{ $post->views ?? 0 }}
    }
  ]
  // "duration": "PT...M...S", // Durasi dihilangkan karena tidak tersedia
  // "expires": "...", // Jika video punya tanggal kadaluarsa
  // "regionsAllowed": "ID", // Jika ada pembatasan geografis
}
</script>

@endsection 