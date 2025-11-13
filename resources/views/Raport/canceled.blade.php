@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-4xl">
        <h1 class="text-3xl font-extrabold mb-8 text-gray-800">Raport odwołań</h1>

        <!-- Filtr dat -->
        <form action="{{ route('raports.cancelled') }}" method="GET" class="mb-8 flex flex-wrap gap-4 items-end">
            <div class="flex flex-col">
                <label for="from" class="text-gray-700 font-medium mb-1">Od:</label>
                <input type="date" name="from" id="from" value="{{ old('from', $from ?? '') }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="flex flex-col">
                <label for="to" class="text-gray-700 font-medium mb-1">Do:</label>
                <input type="date" name="to" id="to" value="{{ old('to', $to ?? '') }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    Filtruj
                </button>
            </div>
        </form>

        @if(isset($total))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white shadow-md rounded-xl p-6 flex flex-col items-center justify-center hover:shadow-lg transition">
                    <div class="text-4xl mb-2">🟢</div>
                    <div class="text-lg font-semibold mb-1">Odwołane przez FEER</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $cancelled_by_feer }}</div>
                </div>
                <div class="bg-white shadow-md rounded-xl p-6 flex flex-col items-center justify-center hover:shadow-lg transition">
                    <div class="text-4xl mb-2">🔴</div>
                    <div class="text-lg font-semibold mb-1">Odwołane przez Beneficjenta</div>
                    <div class="text-2xl font-bold text-red-600">{{ $cancelled_by_client }}</div>
                </div>
                <div class="bg-white shadow-md rounded-xl p-6 flex flex-col items-center justify-center hover:shadow-lg transition">
                    <div class="text-4xl mb-2">📊</div>
                    <div class="text-lg font-semibold mb-1">Łącznie</div>
                    <div class="text-2xl font-bold text-gray-700">{{ $total }}</div>
                </div>
            </div>
        @else
            <p class="text-gray-600 text-center">Brak danych do wyświetlenia. Ustaw filtr dat.</p>
        @endif

        <div class="text-center mt-6">
            <a href="{{ route('raport') }}"
               class="inline-block bg-gray-700 text-white px-8 py-3 rounded-lg shadow hover:bg-gray-800 transition duration-300 font-medium">
                Powrót do raportów
            </a>
        </div>
    </div>
@endsection
