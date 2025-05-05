@props([
    'post',
    'isAboveTheFold' => false,
])

<div class="group bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md hover:translate-y-[-4px]">
    <a href="{{ route('posts.show', $post) }}" class="block relative aspect-video overflow-hidden" wire:navigate>
        <!-- Thumbnail with overlay gradient -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent z-10"></div>
        
        @php
            // Dapatkan URL dari Spatie Media Library
            $thumbnailUrl = $post->getFirstMediaUrl('thumbnail');
        @endphp

        @if($thumbnailUrl)
            <img 
                src="{{ $thumbnailUrl }}" 
                alt="Nonton {{ $post->title }}"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                @if(!$isAboveTheFold) loading="lazy" fetchpriority="low" @else fetchpriority="high" @endif
                decoding="async"
                width="360"
                height="203"
                style="object-position: center center;"
            />
        @else
            <div class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-zinc-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
        
        <!-- View count badge -->
        <div class="absolute bottom-3 left-3 z-20 flex items-center space-x-1 text-xs bg-black/50 text-white px-2 py-1 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span>{{ $post->formatted_views ?? $post->views }}</span>
        </div>
    </a>
    <div class="p-4">
        <a href="{{ route('posts.show', $post) }}" class="block" wire:navigate>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white line-clamp-2 mb-2 hover:text-blue-600 dark:hover:text-blue-400 transition">
                {{ $post->title }}
            </h3>
        </a>
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span class="inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $post->created_at->diffForHumans() }}
            </span>
            @if($post->category)
            <a href="{{ route('categories.show', $post->category->slug) }}" class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" wire:navigate>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                {{ $post->category->name }}
            </a>
            @endif
        </div>
    </div>
</div> 