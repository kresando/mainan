@extends('layouts.app')

@section('title')
    {{ isset($tag) ? '#' . $tag . ' - ' . config('app.name') : 'Tags - ' . config('app.name') }}
@endsection

@section('meta')
    <meta name="description" content="Browse videos with tags on {{ config('app.name') }}">
@endsection

@section('content')
    @livewire('tag-browser', ['slug' => $slug])
@endsection 