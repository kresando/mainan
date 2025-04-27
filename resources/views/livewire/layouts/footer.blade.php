<div class="bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col items-center md:flex-row md:justify-between">
            <div class="mb-6 md:mb-0">
                <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800 dark:text-white" wire:navigate>
                    Layar18
                </a>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    &copy; {{ date('Y') }} Layar18. All rights reserved.
                </p>
            </div>
            
            <div class="flex space-x-6">
                <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400" wire:navigate>
                    Home
                </a>
                <a href="#" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                    Terms
                </a>
                <a href="#" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                    Privacy
                </a>
            </div>
        </div>
    </div>
</div>
