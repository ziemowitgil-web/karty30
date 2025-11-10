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
        /* Focus & accessibility enhancements */
        a:focus, button:focus {
            outline: 2px dashed #fff;
            outline-offset: 2px;
        }
        /* Subtle hover for better WCAG contrast */
        a:hover, button:hover {
            opacity: 0.85;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">

@php
    $accessible = request()->query('accessibility') == 1 || session('accessible_view') == true;
    $userCert = Auth::user()->certificate ?? null;
    $certCN = $userCert['CN'] ?? 'Brak certyfikatu';
@endphp

    <!-- HEADER -->
<header class="bg-gray-900 text-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-6 py-3 flex items-center justify-between">

        <!-- Logo / Title -->
        <a href="{{ route('home') }}" class="text-xl font-bold hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-white">
            {{ config('app.name', 'Laravel') }}
        </a>

        <!-- Desktop Menu -->
        <nav class="hidden md:flex items-center space-x-3" role="navigation" aria-label="Główne menu">
            @auth
                <!-- Quick actions -->
                <a href="{{ route('schedules.create') }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white font-medium transition focus:outline-none focus:ring-2 focus:ring-white">+ Rezerwacja</a>
                <a href="{{ route('clients.create') }}" class="px-3 py-2 bg-green-600 hover:bg-green-700 rounded text-white font-medium transition focus:outline-none focus:ring-2 focus:ring-white">+ Klient</a>

                <!-- Main navigation -->
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

                <!-- Certificate info button -->
                <a href="{{ route('certificateDetailsView') }}" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white" title="Szczegóły certyfikatu X.509">
                    Certyfikat: {{ $certCN }}
                </a>

                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white">Wyloguj</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            @endauth

            @guest
                <a href="{{ route('login') }}" class="px-3 py-2 rounded hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white">Logowanie</a>
            @endguest
        </nav>

        <!-- Mobile Hamburger -->
        <div class="md:hidden flex items-center">
            <button onclick="toggleMenu()" class="focus:outline-none" aria-label="Otwórz menu mobilne">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="md:hidden hidden bg-gray-800 px-4 py-4 space-y-2" role="menu">
        @auth
            <a href="{{ route('schedules.create') }}" class="block px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">+ Rezerwacja</a>
            <a href="{{ route('clients.create') }}" class="block px-4 py-2 rounded bg-green-600 hover:bg-green-700 text-white">+ Klient</a>

            <div class="border-t border-gray-700 my-2"></div>
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

            <!-- Certificate mobile -->
            <a href="{{ route('certificateDetailsView') }}" class="block px-4 py-2 rounded hover:bg-gray-700" title="Szczegóły certyfikatu X.509">
                Certyfikat: {{ $certCN }}
            </a>

            <div class="border-t border-gray-700 my-2"></div>
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

<!-- FOOTER -->
<footer class="bg-gray-900 text-gray-300 py-6 mt-auto">
    <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0 text-center md:text-left">
        <div class="text-sm text-white">
            &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}
            <div class="text-xs text-gray-400">{{ env('APP_VERSION') }}</div>
        </div>

        <div class="px-2 py-1 rounded text-xs font-semibold {{ match(env('APP_ENV', 'local')) {
            'production' => 'bg-green-600',
            'local' => 'bg-yellow-500',
            'staging' => 'bg-orange-500',
            default => 'bg-gray-500',
        } }} text-white">{{ strtoupper(env('APP_ENV', 'LOCAL')) }}</div>
    </div>
</footer>

{!! CookieConsent::scripts() !!}
</body>
</html>
