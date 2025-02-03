<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>PHPServermonitor</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        @if (Route::has('login'))
            <nav class="py-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="p-4">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="p-4">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="p-4">Register</a>
                    @endif
                @endauth
            </nav>
        @endif

        <main class=" p-4">
            Public page
        </main>

        <footer class=" p-4">
            Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
        </footer>
    </body>
</html>
