<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="BIGM Admission Portal – Bangladesh Institute of Governance and Management. Apply online for admission exams, check results, and download admit cards.">
        <meta name="theme-color" content="#1e3a5f">
        <meta property="og:title" content="{{ config('app.name', 'BIGM Admission Portal') }}">
        <meta property="og:description" content="BIGM Admission Portal – Online admission test and application management system">
        <meta property="og:type" content="website">

        <title>{{ config('app.name', 'BIGM Admission Portal') }} – Admission Portal</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen bg-gray-100 flex flex-col">
            <!-- Navbar -->
            @auth
                <nav class="bg-white border-b border-gray-200">
                    @livewire('navigation-menu')
                </nav>
            @endauth

            <!-- Page Heading -->
            @if (isset($header) || View::hasSection('header'))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        @if (isset($header))
                            {{ $header }}
                        @else
                            @yield('header')
                        @endif
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto py-4">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @if (isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </div>
            </main>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
