@props(['height' => null])

<div {{ $attributes->merge(['class' => 'w-full p-4 animate-pulse bg-gray-100 dark:bg-gray-800 rounded-xl overflow-hidden']) }} style="{{ $height ? 'height: '.$height.';' : '' }}">
    @if(isset($slot) && trim((string) $slot))
        {{ $slot }}
    @else
        <!-- Default skeleton layout for a card -->
        <div class="flex flex-col">
            <!-- Image placeholder -->
            <div class="aspect-video bg-gray-300 dark:bg-gray-600 rounded mb-4"></div>
            
            <!-- Title placeholders -->
            <div class="h-5 bg-gray-300 dark:bg-gray-600 rounded mb-2"></div>
            <div class="h-5 w-3/4 bg-gray-300 dark:bg-gray-600 rounded mb-4"></div>
            
            <!-- Details placeholder -->
            <div class="flex justify-between mt-2">
                <div class="h-4 w-1/3 bg-gray-300 dark:bg-gray-600 rounded"></div>
                <div class="h-4 w-1/4 bg-gray-300 dark:bg-gray-600 rounded"></div>
            </div>
        </div>
    @endif
</div> 