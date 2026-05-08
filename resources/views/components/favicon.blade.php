@php
    $faviconUrl = \App\Support\Branding::faviconUrl();
@endphp

@if($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}">
@endif
