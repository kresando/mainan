@extends('layouts.app')

@section('title', 'Search: ' . $query . ' - Layar18')

@section('meta')
<meta name="description" content="Search results for '{{ $query }}' on Layar18">
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Search Header -->
    <div class="mb-10">
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                @if(!$query)
                    Search Videos
                @else
                    Search Results for "{{ $query }}"
                @endif
            </h1>
            
            <!-- Search Form -->
            <form action="{{ route('search') }}" method="GET" class="w-full max-w-3xl">
                <div class="flex rounded-lg overflow-hidden shadow-sm">
                    <input name="q" type="text" class="w-full px-5 py-3 text-gray-900 dark:text-white bg-gray-100 dark:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-blue-500 border-0" placeholder="Search for videos..." value="{{ $query }}">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 transition-colors duration-150 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search
                    </button>
                </div>
            </form>
            
            <!-- Search Results Info -->
            @if($query)
                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    @if($posts->total() > 0)
                        Found {{ $posts->total() }} results
                    @else
                        No results found for "{{ $query }}"
                    @endif
                </div>
            @endif
        </div>
    </div>
    
    <!-- Search Results Grid -->
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
    @elseif($query)
        <div class="rounded-lg bg-white dark:bg-zinc-800 p-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-700 dark:text-gray-300 text-lg mb-4">No videos found for "{{ $query }}"</p>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Try using different keywords or check out our categories.</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}" class="inline-flex items-center px-5 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150" wire:navigate>
                    Go to Homepage
                </a>
            </div>
        </div>
    @endif
</div>
@endsection 