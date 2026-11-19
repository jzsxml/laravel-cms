@extends($portalLayout ?? 'portal.layouts.app')

@section('title', $page->title.' — '.($site['sitename'] ?: config('app.name')))

@push('meta')
    @if($page->description)
        <meta name="description" content="{{ $page->description }}">
    @endif
    <link rel="canonical" href="{{ route('portal.page', $page->slug) }}">
@endpush

@section('content')
@include('portal.pages.'.$template, [
    'page' => $page,
    'contentView' => $contentView ?? null,
    'canDownload' => $canDownload ?? false,
    'member' => $member ?? null,
])
@endsection