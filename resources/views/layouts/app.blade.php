<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Karty 3.0') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-O8Cw4eP+64m19Sm2g7qU/lkGVVwyQ0Hh+8I7sRQGClwVUMZx3mRykGZ2rYbVLqF0cMZwR3l1Z2Pn1D2O0vFqOA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        a:focus, button:focus {
            outline: 3px solid #fff;
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
<body class="bg-gray-50 font-sans text-gray-900 flex flex-col min-h-screen">

@auth
    <header class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 text-white shadow sticky top-0 z-50">
        <div class="container mx-auto px-4 md:px-6 py-4 flex items-center justify-between">

            <!-- Logo -->
            <a href="{{ route('home') }}" class="text-2xl font-bold hover:text-gray-300 transition focus:outline-none focus:ring-2 focus:ring-white">
                <i class="fas fa-hospital-symbol mr-2"></i>{{ config('app.name', 'Karty 3.0') }}
            </a>

            <!-- Desktop Menu -->
            <nav class="hidden md:flex items-center space-x-6" role="navigation" aria-label="Główne menu">
                <a href="{{ route('home') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-home"></i> Strona główna</a>
                <a href="{{ route('schedules.index') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-calendar-alt"></i> Rezerwacje</a>
                <a href="{{ route('consultations.index') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-stethoscope"></i> Konsultacje</a>
                <a href="{{ route('clients.index') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-users"></i> Klienci</a>
                <a href="{{ route('raport') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-file-alt"></i> Raporty</a>

                <a href="{{ route('consultations.certificate.view') }}" class="flex items-center gap-2 px-4 py-2 rounded-3xl bg-amber-500 hover:brightness-90 text-gray-900 font-semibold transition focus:outline-none focus:ring-2 focus:ring-yellow-400"><i class="fas fa-certificate"></i> Certyfikat</a>

                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-sign-out-alt"></i> Wyloguj</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </nav>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center">
                <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="focus:outline-none" aria-label="Otwórz menu mobilne">
                    <i class="fas fa-bars fa-2x"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-gray-800 px-4 py-4 space-y-2" role="menu">
            <a href="{{ route('home') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-home mr-2"></i> Strona główna</a>
            <a href="{{ route('schedules.index') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-calendar-alt mr-2"></i> Rezerwacje</a>
            <a href="{{ route('consultations.index') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-stethoscope mr-2"></i> Konsultacje</a>
            <a href="{{ route('clients.index') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-users mr-2"></i> Klienci</a>
            <a href="{{ route('raport') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-file-alt mr-2"></i> Raporty</a>
            <a href="{{ route('consultations.certificate.view') }}" class="block px-4 py-2 rounded-3xl bg-amber-500 hover:brightness-90 text-gray-900 font-semibold focus:outline-none focus:ring-2 focus:ring-yellow-400"><i class="fas fa-certificate mr-2"></i> Certyfikat</a>
            <div class="border-t border-gray-700 my-2"></div>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" class="block px-4 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white"><i class="fas fa-sign-out-alt mr-2"></i> Wyloguj</a>
            <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </header>
@endauth

<main class="flex-grow container mx-auto px-4 md:px-6 py-8 space-y-8">
    @yield('content')
</main>

<footer class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 text-gray-300 py-8 mt-auto">
    <div class="container mx-auto px-4 md:px-6 flex flex-col md:flex-row justify-between items-center gap-4 md:gap-0">
        <div class="flex flex-col space-y-1 text-center md:text-left">
            <span class="text-sm text-white">&copy; {{ date('Y') }} {{ config('app.name', 'Karty 3.0') }}</span>
            <span class="text-xs text-gray-400">Wersja aplikacji: {{ env('APP_VERSION', 'DEV') }}</span>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-2 md:gap-4">
            <div class="px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-green-500 to-green-700 text-white text-center">
                Środowisko: {{ strtoupper(env('APP_ENV', 'LOCAL')) }}
            </div>
            <div class="text-xs text-gray-400 text-center md:text-left">
                Serwer: {{ request()->getHost() }}
            </div>
        </div>
    </div>
</footer>
</body>
</html>
