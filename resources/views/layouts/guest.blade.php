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
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[##1a1a1a]">
            <!-- Brand Header -->
            <div class="mb-6 flex flex-col items-center">
                <div class="text-5xl mb-2">&#9968;</div>
                <h1 class="text-3xl font-bold text-[#fcc104]">My Mine</h1>
                <p class="text-gray-400 text-sm mt-1">Mine Production Management System</p>
            </div>

            <div class="w-full sm:max-w-md px-6 py-6 bg-white shadow-2xl overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>

            <p class="mt-6 text-gray-500 text-xs">&copy; {{ date('Y') }} My Mine. All rights reserved.</p>
        </div>
    </body>
</html>
