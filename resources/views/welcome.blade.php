@extends('layouts.app')

@section('title', 'Layar18')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Content -->
    <div class="py-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
            Layar18 - Nonton Streaming Bokep Indo &amp; JAV Sub Indo Terbaru
        </h1>
        
        <!-- Bokep Indo Section -->
        @if(isset($indoPosts) && $indoPosts->count() > 0)
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Bokep Indo</h2>
                <a href="{{ route('categories.show', 'bokep-indo') }}" class="text-blue-600 dark:text-blue-400 font-medium hover:underline flex items-center" wire:navigate>
                    View all
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($indoPosts->take(8) as $post)
                    <x-post-card :post="$post" :isAboveTheFold="$loop->index < 8" />
                @endforeach
            </div>
        @else
            <div class="rounded-lg bg-white dark:bg-zinc-800 p-6 text-center mb-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                <p class="text-gray-700 dark:text-gray-300 text-lg">No Bokep Indo posts available yet.</p>
                <a href="{{ route('home') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" wire:navigate>
                    View all posts
                </a>
                </div>
        @endif
        
        <!-- Bokep JAV Section -->
        @if(isset($javPosts) && $javPosts->count() > 0)
            <div class="flex items-center justify-between mb-6 mt-12">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Bokep JAV</h2>
                <a href="{{ route('categories.show', 'bokep-jav') }}" class="text-blue-600 dark:text-blue-400 font-medium hover:underline flex items-center" wire:navigate>
                    View all
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($javPosts->take(8) as $post)
                    <x-post-card :post="$post" :isAboveTheFold="false" />
                @endforeach
            </div>
        @else
            <div class="rounded-lg bg-white dark:bg-zinc-800 p-6 text-center mt-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                <p class="text-gray-700 dark:text-gray-300 text-lg">No Bokep JAV posts available yet.</p>
                <a href="{{ route('home') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" wire:navigate>
                    View all posts
                </a>
        </div>
        @endif
    </div>
</div>
@endsection
