<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="application-name" content="{{ config('app.name', 'Layar18') }}">

        <title>@yield('title', 'Layar18 - Nonton Streaming Bokep Indo & JAV Sub Indo Terbaru')</title>
        
        <meta name="description" content="@yield('meta_description', 'Situs nonton dan streaming video bokep viral Indonesia, JAV sub Indo, Asia, Barat terbaru ' . date('Y') . '. Update setiap hari, kualitas HD.')">
        
        <link rel="canonical" href="{{ url()->current() }}" />
        
        <!-- Favicon -->
        <link rel="icon" href="{{ asset('images/logo.webp') }}" type="image/webp">

        @yield('meta')
        
        <meta property="og:url" content="{{ url()->current() }}" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="@yield('og_title', View::getSections()['title'] ?? config('app.name', 'Layar18'))" />
        <meta property="og:description" content="@yield('og_description', View::getSections()['meta_description'] ?? 'Situs nonton dan streaming video bokep viral Indonesia, JAV sub Indo, Asia, Barat terbaru ' . date('Y') . '.')" />
        <meta property="og:image" content="@yield('og_image', asset('images/logo-og.png'))" />
        <meta property="og:site_name" content="{{ config('app.name', 'Layar18') }}" />
        <meta property="og:locale" content="id_ID" />

        <meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')" />
        <meta name="twitter:title" content="@yield('twitter_title', View::getSections()['title'] ?? config('app.name', 'Layar18'))" />
        <meta name="twitter:description" content="@yield('twitter_description', View::getSections()['meta_description'] ?? 'Situs nonton dan streaming video bokep viral Indonesia, JAV sub Indo, Asia, Barat terbaru ' . date('Y') . '.')" />
        <meta name="twitter:image" content="@yield('twitter_image', asset('images/logo-og.png'))" />

        <!-- Preconnect to critical domains -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://media.layar18.top" crossorigin>
        
        <!-- Preload logo -->
        <link rel="preload" as="image" href="{{ asset('images/logo.webp') }}" type="image/webp">

        <!-- Fonts - optimized loading -->
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
        <noscript>
            <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet">
        </noscript>

        <!-- Styles -->
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        {{-- Schema.org for Website and SearchAction --}}
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "WebSite",
          "name": "{{ config('app.name', 'Layar18') }}",
          "url": "{{ url('/') }}",
          "potentialAction": {
            "@type": "SearchAction",
            "target": {
              "@type": "EntryPoint",
              "urlTemplate": "{{ route('search', [
                  'query' => '{search_term_string}'
              ]) }}"
            },
            "query-input": "required name=search_term_string"
          }
        }
        </script>

        {{-- Script Adsterra Popunder - deferred to not block rendering --}}
        <script defer type='text/javascript' src='//bypassduehardly.com/0c/2f/d3/0c2fd32c4011fbc526498e7b419b27bd.js'></script>

        <!-- Google tag (gtag.js) - deferred -->
        <script defer src="https://www.googletagmanager.com/gtag/js?id=G-2F6YF98D00"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', 'G-2F6YF98D00');
        </script>

    </head>
    <body class="bg-white dark:bg-zinc-900 min-h-screen flex flex-col">
        <!-- Navbar -->
        @livewire('layouts.navbar')
        
        <!-- Page Content -->
        <main class="flex-grow">
            @yield('content')
        </main>

        <!-- Footer -->
        @livewire('layouts.footer')

        @livewireScripts

    </body>
</html> 