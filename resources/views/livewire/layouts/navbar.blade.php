<div x-data="{ 
    mobileMenuOpen: false, 
    darkMode: localStorage.getItem('darkMode') === 'true' || ((!('darkMode' in localStorage)) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    searchOpen: false,
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
        this.updateTheme();
    },
    updateTheme() {
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}" 
x-init="updateTheme()"
x-on:resize.window="mobileMenuOpen = window.innerWidth >= 768 ? false : mobileMenuOpen"
class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 sticky top-0 z-30">
    <!-- Main Navigation Bar -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left: Site Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ route('home') }}" wire:navigate>
                    <img src="{{ asset('images/logo.png') }}" alt="Layar18 Logo - Nonton Streaming Bokep Indo & JAV Sub Indo" class="h-16 w-auto">
                </a>
            </div>
            
            <!-- Middle: Desktop Navigation -->
            <div class="hidden md:flex md:items-center md:space-x-6">
                <a href="{{ route('home') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('home') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' }}" wire:navigate>
                    Home
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 inline-flex items-center">
                        Categories
                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="absolute mt-2 w-56 rounded-lg shadow-lg bg-white dark:bg-zinc-800 ring-1 ring-black ring-opacity-5 z-40">
                        <div class="py-1 max-h-64 overflow-y-auto">
                            @foreach($categories as $category)
                                <a href="{{ route('categories.show', $category->slug) }}" class="block px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700" wire:navigate>
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 inline-flex items-center">
                        Tags
                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="absolute mt-2 w-56 rounded-lg shadow-lg bg-white dark:bg-zinc-800 ring-1 ring-black ring-opacity-5 z-40">
                        <div class="py-1 max-h-64 overflow-y-auto">
                            @foreach($tags as $tag)
                                <a href="{{ route('tags.show', $tag->slug) }}" class="block px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700" wire:navigate>
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right: Search and Theme Toggle -->
            <div class="flex items-center space-x-4">
                <!-- Desktop Search - Always visible -->
                <div class="hidden md:flex items-center relative">
                    <form action="{{ route('search') }}" method="GET" class="relative">
                        <div class="flex">
                            <input name="q" type="text" class="w-48 px-3 py-1.5 text-sm text-gray-900 dark:text-white bg-gray-100 dark:bg-zinc-700 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search videos..." value="{{ $searchQuery }}">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-r-md transition-colors duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Mobile Search Toggle -->
                <button @click="searchOpen = !searchOpen" class="md:hidden p-1 rounded-full text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                
                <!-- Theme Toggle -->
                <button @click="toggleDarkMode()" class="p-1 rounded-full text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 focus:outline-none">
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
                
                <!-- Mobile menu button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="md:hidden p-1 rounded-md text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 focus:outline-none">
                    <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Search Bar (shown/hidden) -->
    <div x-show="searchOpen" x-transition class="md:hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 p-3">
        <form action="{{ route('search') }}" method="GET" class="w-full">
            <div class="flex">
                <input name="q" type="text" class="w-full px-4 py-2 text-sm text-gray-900 dark:text-white bg-gray-100 dark:bg-zinc-700 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search videos..." value="{{ $searchQuery }}">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 rounded-r-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Mobile menu -->
    <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800" x-cloak>
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('home') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' }}" wire:navigate>
                Home
            </a>
            
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                    Categories
                </button>
                <div x-show="open" class="pl-4">
                    @foreach($categories as $category)
                        <a href="{{ route('categories.show', $category->slug) }}" class="block px-3 py-2 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400" wire:navigate>
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
            
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                    Tags
                </button>
                <div x-show="open" class="pl-4">
                    @foreach($tags as $tag)
                        <a href="{{ route('tags.show', $tag->slug) }}" class="block px-3 py-2 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400" wire:navigate>
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
