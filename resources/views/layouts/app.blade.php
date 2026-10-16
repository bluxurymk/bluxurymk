@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' — '.config('salon.name') : config('salon.name').' — '.config('salon.city') }}</title>
        <meta name="description" content="{{ $description ?? config('salon.name').', '.config('salon.city').'. '.__('site.tagline') }}">
        <meta name="theme-color" content="#0e0c0a">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col">
        <a href="#main" class="sr-only z-[60] rounded-full bg-gold-500 px-4 py-2 text-marble-950 focus:not-sr-only focus:fixed focus:top-4 focus:left-4">{{ __('site.nav.skip') }}</a>

        <x-navbar />

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        <x-footer />
    </body>
</html>
