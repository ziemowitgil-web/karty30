<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
            menu.classList.toggle('block');
        }
    </script>
    {!! CookieConsent::styles() !!}
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">

@php
    $accessible = request()->query('accessibility') == 1 || session('accessible_view') == true;
@endphp

    <!-- Header -->
<header class="bg-gray-900 text-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-6 py-3 flex items-center justify-between">

        <!-- Logo / Title -->
        @unless($accessible)
            <span class="text-xl font-bold">{{ config('app.name', 'Laravel') }}</span>
        @endunless

        <!-- Desktop menu -->
        <nav class="hidden md:flex items-center space-x-4">
            @auth
                <a href="{{ route('home') }}" class="px-4 py-2 rounded hover:bg-gray-700 transition">Strona główna</a>
                <a href="{{ route('schedules.index') }}" class="px-4 py-2 rounded hover:bg-gray-700 transition">Rezerwacje</a>
                <a href="{{ route('consultations.index') }}" class="px-4 py-2 rounded hover:bg-gray-700 transition">Konsultacje</a>

                @unless($accessible)
                    <a href="{{ route('clients.index') }}" class="px-4 py-2 rounded hover:bg-gray-700 transition">Klienci</a>
                    <a href="{{ route('raport') }}" class="px-4 py-2 rounded hover:bg-gray-700 transition">Raporty</a>
                @endunless

                <!-- Admin dropdown -->
                @if(auth()->user()->is_admin ?? true)
                    <div class="relative group">
                        <button class="px-4 py-2 rounded hover:bg-gray-700 flex items-center space-x-1 transition">
                            Admin
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute left-0 mt-1 w-40 bg-gray-800 rounded shadow-lg hidden group-hover:block z-50">
                            <a href="{{ route('logs') }}" class="block px-4 py-2 text-white hover:bg-gray-700 transition">Logi</a>
                        </div>
                    </div>
                @endif

                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="px-4 py-2 rounded hover:bg-gray-700 transition">Wyloguj</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            @endauth

            @guest
                <a href="{{ route('login') }}" class="px-4 py-2 rounded hover:bg-gray-700 transition">Logowanie</a>
                @if(env('APP_LICENSE') && !$accessible)
                    <span class="text-gray-300 text-xs ml-2">Organizacja: {{ env('APP_LICENSE') }}</span>
                @endif
            @endguest
        </nav>

        <!-- Hamburger mobile -->
        <div class="md:hidden flex items-center">
            <button onclick="toggleMenu()" class="focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" aria-label="Otwórz menu mobilne">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="md:hidden hidden bg-gray-800 px-4 py-4 space-y-2">
        @auth
            <a href="{{ route('home') }}" class="block px-4 py-2 rounded hover:bg-gray-700">Strona główna</a>
            <a href="{{ route('schedules.index') }}" class="block px-4 py-2 rounded hover:bg-gray-700">Rezerwacje</a>
            <a href="{{ route('consultations.index') }}" class="block px-4 py-2 rounded hover:bg-gray-700">Konsultacje</a>

            @unless($accessible)
                <a href="{{ route('clients.index') }}" class="block px-4 py-2 rounded hover:bg-gray-700">Klienci</a>
                <a href="{{ route('raport') }}" class="block px-4 py-2 rounded hover:bg-gray-700">Raporty</a>
            @endunless

            @if(auth()->user()->is_admin ?? true)
                <a href="{{ route('logs') }}" class="block px-4 py-2 rounded hover:bg-gray-700">Logi</a>
            @endif

            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" class="block px-4 py-2 rounded hover:bg-gray-700">Wyloguj</a>
            <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        @endauth

        @guest
            <a href="{{ route('login') }}" class="block px-4 py-2 rounded hover:bg-gray-700">Logowanie</a>
        @endguest
    </div>
</header>

<!-- Main content -->
<main class="flex-grow container mx-auto px-6 py-6">
    @yield('content')
</main>

<!-- Footer -->
<footer class="bg-gray-900 text-gray-300 py-6 mt-auto">
    <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0 text-center md:text-left">
        <div class="text-sm text-white">&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}   <div class="text-xs text-gray-400">{{ env('APP_VERSION') }}</div>
        </div>
        @unless($accessible)
            @if(env('APP_LICENSE'))
            @endif
        @endunless
        <div class="px-2 py-1 rounded text-xs font-semibold {{ match(env('APP_ENV', 'local')) {
            'production' => 'bg-green-600',
            'local' => 'bg-yellow-500',
            'staging' => 'bg-orange-500',
            default => 'bg-gray-500',
        } }} text-white">{{ strtoupper(env('APP_ENV', 'LOCAL')) }} <br> </div>


    </div>
</footer>
{!! CookieConsent::scripts() !!}
</body>
</html>
