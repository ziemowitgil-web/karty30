<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Karty 3.0') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Cookie Consent -->
    {!! CookieConsent::styles() !!}

    <script>
        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }
    </script>

    <style>
        a:focus, button:focus {
            outline: 2px dashed #fff;
            outline-offset: 2px;
        }
        .truncate-title {
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">

@php
    $accessible = request()->query('accessibility') == 1 || session('accessible_view') == true;
    $certDir = storage_path('app/certificates');
    $certFile = $certDir . '/' . Auth::user()->id . '_user_cert.pem';
    $certExists = file_exists($certFile);
    $certDisplay = $certExists ? basename($certFile) : 'Brak certyfikatu';
@endphp

    <!-- HEADER -->
<header class="bg-gray-900 text-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4 md:px-6 py-3 flex items-center justify-between">

        <!-- Logo / nazwa aplikacji -->
        <a href="{{ route('home') }}" class="text-2xl font-bold hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-white" aria-label="Strona główna">
            {{ config('app.name', 'Karty 3.0') }}
        </a>

        <!-- Desktop menu -->
        <nav class="hidden md:flex items-center space-x-4" role="navigation" aria-label="Główne menu">
            @auth
                <!-- Akcje szybkie -->
                <a href="{{ route('schedules.create') }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white font-medium transition focus:outline-none focus:ring-2 focus:ring-white">+ Rezerwacja</a>
                <a href="{{ route('clients.create') }}" class="px-3 py-2 bg-green-600 hover:bg-green-700 rounded text-white font-medium transition focus:outline-none focus:ring-2 focus:ring-white">+ Klient</a>

                <!-- Nawigacja główna -->
                <a href="{{ route('home') }}" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white">Strona główna</a>
                <a href="{{ route('schedules.index') }}" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white">Rezerwacje</a>
                <a href="{{ route('consultations.index') }}" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white">Konsultacje</a>

                @unless($accessible)
                    <a href="{{ route('clients.index') }}" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white">Klienci</a>
                    <a href="{{ route('raport') }}" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white">Raporty</a>
                @endunless

                @if(auth()->user()->is_admin ?? true)
                    <a href="{{ route('logs') }}" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white">Logi</a>
                @endif

                <!-- Informacja o certyfikacie -->
                <div class="px-3 py-2 rounded bg-yellow-500 text-gray-900 font-semibold text-sm truncate-title" title="{{ $certDisplay }}">
                    Certyfikat: {{ $certDisplay }}
                </div>

                <!-- Wylogowanie -->
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white">Wyloguj</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            @endauth

            @guest
                <a href="{{ route('login') }}" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white">Logowanie</a>
            @endguest
        </nav>

        <!-- Mobile hamburger -->
        <div class="md:hidden flex items-center">
            <button onclick="toggleMenu()" class="focus:outline-none" aria-label="Otwórz menu mobilne">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="md:hidden hidden bg-gray-800 px-4 py-4 space-y-2" role="menu">
        @auth
            <a href="{{ route('schedules.create') }}" class="block px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white focus:outline-none focus:ring-2 focus:ring-white">+ Rezerwacja</a>
            <a href="{{ route('clients.create') }}" class="block px-4 py-2 rounded bg-green-600 hover:bg-green-700 text-white focus:outline-none focus:ring-2 focus:ring-white">+ Klient</a>

            <div class="border-t border-gray-700 my-2"></div>
            <a href="{{ route('home') }}" class="block px-4 py-2 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white">Strona główna</a>
            <a href="{{ route('schedules.index') }}" class="block px-4 py-2 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white">Rezerwacje</a>
            <a href="{{ route('consultations.index') }}" class="block px-4 py-2 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white">Konsultacje</a>

            @unless($accessible)
                <a href="{{ route('clients.index') }}" class="block px-4 py-2 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white">Klienci</a>
                <a href="{{ route('raport') }}" class="block px-4 py-2 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white">Raporty</a>
            @endunless

            @if(auth()->user()->is_admin ?? true)
                <a href="{{ route('logs') }}" class="block px-4 py-2 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white">Logi</a>
            @endif

            <!-- Info certyfikat -->
            <div class="block px-4 py-2 rounded bg-yellow-500 text-gray-900 font-semibold text-sm truncate-title" title="{{ $certDisplay }}">
                Certyfikat: {{ $certDisplay }}
            </div>

            <div class="border-t border-gray-700 my-2"></div>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" class="block px-4 py-2 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white">Wyloguj</a>
            <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        @endauth

        @guest
            <a href="{{ route('login') }}" class="block px-4 py-2 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white">Logowanie</a>
        @endguest
    </div>
</header>

<!-- Main content -->
<main class="flex-grow container mx-auto px-4 md:px-6 py-6">
    @yield('content')
</main>

<!-- FOOTER -->
<footer class="bg-gray-900 text-gray-300 py-8 mt-auto">
    <div class="container mx-auto px-4 md:px-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-0">
        <div class="flex flex-col space-y-1 text-center md:text-left">
            <span class="text-sm text-white">&copy; {{ date('Y') }} {{ config('app.name', 'Karty 3.0') }}</span>
            <span class="text-xs text-gray-400">Wersja aplikacji: {{ env('APP_VERSION', 'DEV') }}</span>
        </div>

        <div class="flex flex-col md:flex-row items-center gap-2 md:gap-4">
            <div class="px-3 py-1 rounded text-xs font-semibold {{ match(env('APP_ENV', 'local')) {
                'production' => 'bg-green-600',
                'local' => 'bg-yellow-500',
                'staging' => 'bg-orange-500',
                default => 'bg-gray-500',
            } }} text-white text-center">
                Środowisko: {{ strtoupper(env('APP_ENV', 'LOCAL')) }}
            </div>
            <div class="text-xs text-gray-400 text-center md:text-left">
                Serwer: {{ request()->getHost() }}
            </div>
        </div>
    </div>
</footer>

{!! CookieConsent::scripts() !!}
</body>
</html>
