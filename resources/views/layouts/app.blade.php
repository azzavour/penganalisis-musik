<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Music Manager') - {{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        /* Simple transition for sidebar */
        .sidebar-link {
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
        }
    </style>
</head>
<body class="h-full font-sans antialiased">
    <div class="flex h-full">
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <div class="h-16 flex items-center px-6 border-b border-gray-200">
                <a href="#" class="flex items-center space-x-2">
                    <svg class="h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z" />
                    </svg>
                    <span class="text-xl font-bold text-gray-800">{{ config('app.name', 'Laravel') }}</span>
                </a>
            </div>

            <nav class="flex-1 p-4 space-y-2">
    {{-- Tautan ke Music Manager --}}
    <a href="{{ route('music-manager.index') }}" 
       class="sidebar-link flex items-center px-4 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('music-manager.index') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
        <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
        </svg>
        Music Manager
    </a>
    {{-- Tautan ke Halaman Upload Baru --}}
    <a href="{{ route('music-manager.create') }}" 
       class="sidebar-link flex items-center px-4 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('music-manager.create') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
        <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        New Music Order
    </a>
</nav>
        </aside>

        <div class="flex-1 flex flex-col">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-end px-6">
    {{-- Kosongkan area ini atau tambahkan elemen lain jika perlu nanti --}}
</header>
            
            <main class="flex-1 overflow-y-auto bg-gray-100">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>