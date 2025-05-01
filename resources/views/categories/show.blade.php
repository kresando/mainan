@extends('layouts.app')

{{-- SEO Title --}}
@section('title', $title ?? ($category->name . ' - Layar18'))

{{-- SEO Meta Description --}}
@section('meta_description', $description ?? ('Nonton Kumpulan Video ' . $category->name . ' terbaru di Layar18.'))

@section('meta')
    @parent {{-- Sertakan meta default dari app.blade.php --}}
    {{-- Tambahkan meta OG/Twitter spesifik kategori jika perlu --}}
    <meta property="og:title" content="{{ $title ?? ($category->name . ' - Layar18') }}" />
    <meta property="og:description" content="{{ $description ?? ('Nonton Kumpulan Video ' . $category->name . ' terbaru di Layar18.') }}" />
    {{-- Mungkin tambahkan og:image jika kategori punya gambar? --}}
    
    <meta name="twitter:title" content="{{ $title ?? ($category->name . ' - Layar18') }}" />
    <meta name="twitter:description" content="{{ $description ?? ('Nonton Kumpulan Video ' . $category->name . ' terbaru di Layar18.') }}" />
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="flex flex-col gap-6 mb-10">
        <div class="flex items-center gap-3">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $category->name }}</h1>
            <div class="rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 text-sm font-medium">
                {{ $posts->total() }} videos
            </div>
        </div>
        
        <!-- Enhanced Filters and Sorting -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Filter Label -->
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Filter & Sort:
                </div>
                
                <!-- Controls -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Time Filter Dropdown - Enhanced -->
                    <div x-data="{ open: false }" class="relative">
                        <button 
                            @click="open = !open" 
                            @click.away="open = false"
                            class="w-full sm:w-auto flex items-center justify-between gap-2 bg-gray-50 dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 px-4 py-2.5 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-600 transition-colors duration-150"
                        >
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ ucfirst($timeFilter === 'all' ? 'All Time' : $timeFilter) }}</span>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-100" 
                            x-transition:enter-start="transform opacity-0 scale-95" 
                            x-transition:enter-end="transform opacity-100 scale-100" 
                            x-transition:leave="transition ease-in duration-75" 
                            x-transition:leave-start="transform opacity-100 scale-100" 
                            x-transition:leave-end="transform opacity-0 scale-95" 
                            class="absolute left-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 z-20"
                        >
                            <div class="py-1">
                                <a href="{{ route('categories.show', ['slug' => $category->slug, 'sort' => $sort, 'filter' => 'all']) }}" 
                                   class="block px-4 py-2.5 text-sm {{ $timeFilter === 'all' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700' }}"
                                   wire:navigate>
                                    All Time
                                </a>
                                <a href="{{ route('categories.show', ['slug' => $category->slug, 'sort' => $sort, 'filter' => 'today']) }}" 
                                   class="block px-4 py-2.5 text-sm {{ $timeFilter === 'today' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700' }}"
                                   wire:navigate>
                                    Today
                                </a>
                                <a href="{{ route('categories.show', ['slug' => $category->slug, 'sort' => $sort, 'filter' => 'week']) }}" 
                                   class="block px-4 py-2.5 text-sm {{ $timeFilter === 'week' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700' }}"
                                   wire:navigate>
                                    This Week
                                </a>
                                <a href="{{ route('categories.show', ['slug' => $category->slug, 'sort' => $sort, 'filter' => 'month']) }}" 
                                   class="block px-4 py-2.5 text-sm {{ $timeFilter === 'month' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700' }}"
                                   wire:navigate>
                                    This Month
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sort Dropdown - Enhanced -->
                    <div x-data="{ open: false }" class="relative">
                        <button 
                            @click="open = !open" 
                            @click.away="open = false"
                            class="w-full sm:w-auto flex items-center justify-between gap-2 bg-gray-50 dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 px-4 py-2.5 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-600 transition-colors duration-150"
                        >
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                </svg>
                                <span>
                                    {{ $sort === 'latest' ? 'Latest' : ($sort === 'views' ? 'Most Viewed' : 'Random') }}
                                </span>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-100" 
                            x-transition:enter-start="transform opacity-0 scale-95" 
                            x-transition:enter-end="transform opacity-100 scale-100" 
                            x-transition:leave="transition ease-in duration-75" 
                            x-transition:leave-start="transform opacity-100 scale-100" 
                            x-transition:leave-end="transform opacity-0 scale-95" 
                            class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 z-20"
                        >
                            <div class="py-1">
                                <a href="{{ route('categories.show', ['slug' => $category->slug, 'sort' => 'latest', 'filter' => $timeFilter]) }}" 
                                   class="block px-4 py-2.5 text-sm {{ $sort === 'latest' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700' }}"
                                   wire:navigate>
                                    Latest
                                </a>
                                <a href="{{ route('categories.show', ['slug' => $category->slug, 'sort' => 'views', 'filter' => $timeFilter]) }}" 
                                   class="block px-4 py-2.5 text-sm {{ $sort === 'views' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700' }}"
                                   wire:navigate>
                                    Most Viewed
                                </a>
                                <a href="{{ route('categories.show', ['slug' => $category->slug, 'sort' => 'random', 'filter' => $timeFilter]) }}" 
                                   class="block px-4 py-2.5 text-sm {{ $sort === 'random' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700' }}"
                                   wire:navigate>
                                    Random
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Post Grid -->
    @if($posts->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            @foreach($posts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @else
        <div class="rounded-lg bg-white dark:bg-zinc-800 p-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-gray-700 dark:text-gray-300 text-lg mb-4">No videos found with the selected filters.</p>
            <a href="{{ route('categories.show', $category->slug) }}" class="inline-flex items-center px-5 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150" wire:navigate>
                Clear filters
            </a>
        </div>
    @endif
    
    <!-- Statistics Banner -->
    <div class="mt-12 bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Site Stats</h2>
        
        <div class="grid grid-cols-2 gap-6">
            <div class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                <span class="text-gray-600 dark:text-gray-400 text-sm mb-1">Total Videos</span>
                <span class="font-medium text-2xl text-gray-900 dark:text-white">{{ $totalPosts }}</span>
            </div>
            <div class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                <span class="text-gray-600 dark:text-gray-400 text-sm mb-1">Total Views</span>
                <span class="font-medium text-2xl text-gray-900 dark:text-white">{{ number_format($totalViews) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Schema.org JSON-LD for CollectionPage --}}
@php
    $schemaDescription = addslashes($description ?? ('Nonton Kumpulan Video ' . $category->name . ' terbaru di Layar18.'));
@endphp
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "{{ addslashes($title ?? ($category->name . ' - Layar18')) }}",
    "description": "{{ $schemaDescription }}",
    "url": "{{ url()->current() }}" 
    // Anda bisa menambahkan "image" jika kategori punya gambar
    // Anda bisa menambahkan "mainEntity" dengan type "ItemList" jika ingin 
    // mencantumkan beberapa video pertama di halaman ini.
}
</script>

@endsection 