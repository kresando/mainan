@extends('layouts.app')

{{-- SEO Title --}}
@section('title')
    {{ $title ?? ('#' . $tag->name . ' - Layar18') }} {{-- Assume $tag object and $title are passed --}}
@endsection

{{-- SEO Meta Description & OG/Twitter Overrides --}}
@section('meta_description')
    {{ $description ?? ('Nonton Kumpulan Video dengan tag #' . $tag->name . ' terbaru di Layar18.') }} {{-- Assume $tag object and $description are passed --}}
@endsection

@section('meta')
    @parent {{-- Include default meta from app.blade.php --}}
    
    {{-- Open Graph Overrides --}}
    <meta property="og:title" content="{{ $title ?? ('#' . $tag->name . ' - Layar18') }}" />
    <meta property="og:description" content="{{ $description ?? ('Nonton Kumpulan Video dengan tag #' . $tag->name . ' terbaru di Layar18.') }}" />
    {{-- You could add an og:image if tags have associated images --}}

    {{-- Twitter Card Overrides --}}
    <meta name="twitter:title" content="{{ $title ?? ('#' . $tag->name . ' - Layar18') }}" />
    <meta name="twitter:description" content="{{ $description ?? ('Nonton Kumpulan Video dengan tag #' . $tag->name . ' terbaru di Layar18.') }}" />
    {{-- You could add a twitter:image if tags have associated images --}}
@endsection

@section('content')
    {{-- Pass the tag object to Livewire if needed for H1 etc. inside the component --}}
    @livewire('tag-browser', ['slug' => $slug, 'tag' => $tag]) 
@endsection 