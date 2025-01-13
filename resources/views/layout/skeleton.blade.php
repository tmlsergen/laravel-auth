<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', config('app.name'))</title>

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @yield('scripts')
    </head>
    <body class="min-h-screen bg-[#1B1E24] text-gray-200">
        <!-- Header -->
        <header class="border-b border-gray-700">
            <div class="container mx-auto px-4 py-4">
                @include('layout.header')
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex min-h-[calc(100vh-73px)] items-center justify-center">
            <div class="w-full {{ Route::is('login') ? 'max-w-md' : (Route::is('dashboard') ? 'max-w-5xl' : 'max-w-2xl') }} rounded-lg border border-gray-700 bg-[#1F2937] p-8">
                @yield('content')
            </div>
        </main>
    </body>
</html>
