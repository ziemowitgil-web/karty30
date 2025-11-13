@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6 text-center">Raporty</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">

            <!-- Raport odwołanych terminów -->
            <a href="{{ route('raports.cancelled') }}" class="block bg-blue-500 text-white px-4 py-3 rounded text-center font-semibold">
                Raport odwołanych terminów
            </a>

            <!-- Raport konsultacje w tym miesiącu -->
            <a href="{{ route('raports.approvedThisMonth') }}" class="block bg-green-500 text-white px-4 py-3 rounded text-center font-semibold">
                Raport konsultacji w tym miesiącu
            </a>

            <!-- Raport konsultacje w poprzednim miesiącu -->
            <a href="{{ route('raports.approvedLastMonth') }}" class="block bg-green-400 text-white px-4 py-3 rounded text-center font-semibold">
                Raport konsultacji w poprzednim miesiącu
            </a>

            <!-- Raport MRPiPS PDF -->
            <a href="{{ route('raports.monthlyReportMRPIPS') }}" class="block bg-pink-500 text-white px-4 py-3 rounded text-center font-semibold">
                Raport MRPiPS (PDF)
            </a>

            <!-- Raport czarnej listy -->
            <a href="{{ route('raports.blacklist') }}" class="block bg-red-500 text-white px-4 py-3 rounded text-center font-semibold">
                Czarna lista
            </a>

            <!-- Nowe raporty – nieaktywne -->
            <div class="block bg-gray-300 text-gray-700 px-4 py-3 rounded text-center font-semibold cursor-not-allowed">
                Konsultacje według użytkownika (w przygotowaniu)
            </div>

            <div class="block bg-gray-300 text-gray-700 px-4 py-3 rounded text-center font-semibold cursor-not-allowed">
                Konsultacje według typu/usługi (w przygotowaniu)
            </div>

            <div class="block bg-gray-300 text-gray-700 px-4 py-3 rounded text-center font-semibold cursor-not-allowed">
                Nowi vs powracający klienci (w przygotowaniu)
            </div>

            <div class="block bg-gray-300 text-gray-700 px-4 py-3 rounded text-center font-semibold cursor-not-allowed">
                Aktywni klienci (w przygotowaniu)
            </div>

            <div class="block bg-gray-300 text-gray-700 px-4 py-3 rounded text-center font-semibold cursor-not-allowed">
                Odwołania według użytkownika (w przygotowaniu)
            </div>

        </div>

        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-block bg-gray-500 text-white px-6 py-2 rounded font-medium">
                Powrót do strony głównej
            </a>
        </div>
    </div>
@endsection
