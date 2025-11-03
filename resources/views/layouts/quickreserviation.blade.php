<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Szybka rezerwacja</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">

<!-- Header -->
<header class="bg-blue-600 text-white shadow-md p-4">
    <h1 class="text-xl font-semibold text-center">Szybka rezerwacja konsultacji</h1>
</header>

<!-- Main content -->
<main class="flex-grow container mx-auto px-4 py-6">
    @yield('content')
</main>

<!-- Footer -->
<footer class="bg-gray-200 text-gray-700 py-4 text-center text-sm">
    &copy; {{ date('Y') }} Szybka rezerwacja
</footer>

</body>
</html>
