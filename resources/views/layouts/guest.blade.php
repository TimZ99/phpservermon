<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PHPServerMonitor') }}</title>

        <meta name="description" content="PHP Server Monitor">
        <meta name="robots" content="noindex" />
        <!--<link rel="manifest" href="./manifest.json">-->
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="PSM">
        <link rel="apple-touch-icon" href="/phpservermon.png">
        <meta name="msapplication-TileImage" content="./phpservermon.png">
        <meta name="msapplication-TileColor" content="#424242">

        <meta name="theme-color" content="#424242">
        <link rel="icon" type="image/x-icon" href="/favicon.ico" />
        <link rel="icon" type="image/png" href="/favicon.png" />
        <link rel="apple-touch-icon" href="/favicon.png" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        @include('layouts.navigation')
        <main> 
            {{ $slot }}
        </main>
        <footer class="fixed-bottom" role="contentinfo">
            <div class="container">
                <span class="text-body-secondary">
                    Powered by
                    <a href="https://github.com/phpservermon/phpservermon/" target="_blank" rel="noopener">
                        PHPServerMonitor.
                    </a>
                    Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                </span>
            </div>
        </footer>
    </body>
</html>
