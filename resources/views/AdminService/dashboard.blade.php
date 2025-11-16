@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-6 py-6">
        <h1 class="text-3xl font-bold mb-6">Panel Administratora</h1>

        {{-- Karty statystyk --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- Liczba użytkowników --}}
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-2">Użytkownicy</h2>
                <p class="text-3xl font-bold">{{ $userCount }}</p>
                <a href="{{ route('admin.users.list') }}" class="mt-4 inline-block text-blue-600 hover:underline text-sm">
                    Zobacz listę użytkowników
                </a>
            </div>

            {{-- Liczba logów --}}
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-2">Logi aktywności</h2>
                <p class="text-3xl font-bold">{{ $logCount }}</p>
                <a href="{{ route('admin.logs') }}" class="mt-4 inline-block text-blue-600 hover:underline text-sm">
                    Zobacz logi
                </a>
            </div>

            {{-- Panel .env --}}
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-2">Ustawienia systemowe</h2>
                <p class="text-gray-600">Zmienna .env</p>
                <a href="{{ route('admin.env.update') }}" class="mt-4 inline-block text-blue-600 hover:underline text-sm">
                    Edytuj zmienne
                </a>
            </div>
        </div>

        {{-- Dodatkowe akcje --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-2">Szybkie akcje</h2>
                <ul class="list-disc pl-5 text-gray-700">
                    <li><a href="{{ route('admin.users.list') }}" class="text-blue-600 hover:underline">Zarządzaj użytkownikami</a></li>
                    <li><a href="{{ route('admin.logs') }}" class="text-blue-600 hover:underline">Przeglądaj logi</a></li>
                    <li><a href="{{ route('admin.env.update') }}" class="text-blue-600 hover:underline">Zarządzaj zmiennymi .env</a></li>
                </ul>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-2">Pomoc i dokumentacja</h2>
                <ul class="list-disc pl-5 text-gray-700">
                    <li><a href="#" class="text-blue-600 hover:underline">Dokumentacja systemu</a></li>
                    <li><a href="#" class="text-blue-600 hover:underline">Wsparcie techniczne</a></li>
                    <li><a href="#" class="text-blue-600 hover:underline">FAQ</a></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
