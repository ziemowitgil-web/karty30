@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-extrabold mb-8 text-center text-gray-800">Raporty</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <!-- Raport odwołanych terminów -->
            <a href="{{ route('raports.cancelled') }}"
               class="block bg-blue-500 text-white px-6 py-5 rounded-lg shadow hover:bg-blue-600 transition duration-300 text-center font-semibold">
                Raport odwołanych terminów
            </a>

            <!-- Raport konsultacje w tym miesiącu -->
            <a href="{{ route('raports.approvedThisMonth') }}"
               class="block bg-green-600 text-white px-6 py-5 rounded-lg shadow hover:bg-green-700 transition duration-300 text-center font-semibold">
                Raport konsultacji w tym miesiącu
            </a>

            <!-- Raport konsultacje w poprzednim miesiącu -->
            <a href="{{ route('raports.approvedLastMonth') }}"
               class="block bg-green-500 text-white px-6 py-5 rounded-lg shadow hover:bg-green-600 transition duration-300 text-center font-semibold">
                Raport konsultacji w poprzednim miesiącu
            </a>

            <!-- Raport MRPiPS PDF -->
            <a href="{{ route('raports.monthlyReportMRPIPS') }}"
               class="block bg-pink-700 text-white px-6 py-5 rounded-lg shadow hover:bg-pink-800 transition duration-300 text-center font-semibold">
                Raport konsultacji do MRPiPS (PDF)
            </a>

            <!-- Raport czarnej listy -->
            <a href="{{ route('raports.blacklist') }}"
               class="block bg-red-500 text-white px-6 py-5 rounded-lg shadow hover:bg-red-600 transition duration-300 text-center font-semibold">
                Czarna lista (CL)
            </a>

            <!-- NOWE raporty – nieaktywne -->
            <div class="block bg-gray-300 text-gray-600 px-6 py-5 rounded-lg shadow cursor-not-allowed text-center font-semibold">
                Konsultacje według użytkownika (w przygotowaniu)
            </div>
            <div class="block bg-gray-300 text-gray-600 px-6 py-5 rounded-lg shadow cursor-not-allowed text-center font-semibold">
                Konsultacje według typu/usługi (w przygotowaniu)
            </div>
            <div class="block bg-gray-300 text-gray-600 px-6 py-5 rounded-lg shadow cursor-not-allowed text-center font-semibold">
                Nowi vs powracający klienci (w przygotowaniu)
            </div>
            <div class="block bg-gray-300 text-gray-600 px-6 py-5 rounded-lg shadow cursor-not-allowed text-center font-semibold">
                Aktywni klienci (w przygotowaniu)
            </div>
            <div class="block bg-gray-300 text-gray-600 px-6 py-5 rounded-lg shadow cursor-not-allowed text-center font-semibold">
                Odwołania według użytkownika (w przygotowaniu)
            </div>
        </div>

        <!-- Powrót do strony głównej -->
        <div class="text-center">
            <a href="{{ route('home') }}"
               class="inline-block bg-gray-500 text-white px-8 py-3 rounded-lg shadow hover:bg-gray-600 transition duration-300 font-medium">
                Powrót do strony głównej
            </a>
        </div>
    </div>
@endsection
