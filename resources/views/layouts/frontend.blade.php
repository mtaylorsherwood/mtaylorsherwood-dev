<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-gray-800">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 text-white">
                                <a href="{{ route('index') }}">
                                    M Taylor-Sherwood
                                </a>
                            </div>
{{--                            <div class="hidden sm:ml-6 sm:block">--}}
{{--                                <div class="flex space-x-4">--}}
{{--                                    <!-- Current: "bg-gray-900 text-white", Default: "text-gray-300 hover:bg-gray-700 hover:text-white" -->--}}
{{--                                    <a href="{{ route('index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Home</a>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
            <footer class="bg-gray-800">
                <div class="mx-auto max-w-7xl px-6 pb-16 pt-2 sm:pt-6 lg:px-8 lg:pt-8">
                    <div class="mt-4 border-t border-white/10 pt-2 md:flex md:items-center md:justify-between">
                        <p class="mt-2 text-xs leading-5 text-gray-400 md:order-1 md:mt-0">&copy; 1986 - {{ date('Y') }} M Taylor-Sherwood. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
