@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-4xl">

        <h1 class="text-2xl font-bold mb-6 text-gray-800 text-center">Raport odwołań</h1>

        <!-- Filtr dat -->
        <form action="{{ route('raports.cancelled') }}" method="GET" class="mb-6 flex flex-wrap gap-4 items-end justify-center">
            <div class="flex flex-col">
                <label for="from" class="font-medium mb-1">Od:</label>
                <input type="date" name="from" id="from" value="{{ old('from', $from ?? '') }}" class="border px-2 py-1 rounded">
            </div>
            <div class="flex flex-col">
                <label for="to" class="font-medium mb-1">Do:</label>
                <input type="date" name="to" id="to" value="{{ old('to', $to ?? '') }}" class="border px-2 py-1 rounded">
            </div>
            <div>
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded font-medium">Filtruj</button>
            </div>
        </form>

        @if(isset($total))
            <table class="w-full border border-gray-400 mb-6">
                <thead>
                <tr class="bg-gray-200">
                    <th class="border px-3 py-2 text-left">Typ odwołania</th>
                    <th class="border px-3 py-2 text-left">Liczba</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="border px-3 py-2">Odwołane przez FEER</td>
                    <td class="border px-3 py-2">{{ $cancelled_by_feer }}</td>
                </tr>
                <tr>
                    <td class="border px-3 py-2">Odwołane przez Beneficjenta</td>
                    <td class="border px-3 py-2">{{ $cancelled_by_client }}</td>
                </tr>
                <tr class="font-semibold">
                    <td class="border px-3 py-2">Łącznie</td>
                    <td class="border px-3 py-2">{{ $total }}</td>
                </tr>
                </tbody>
            </table>
        @else
            <p class="text-gray-700 text-center">Brak danych do wyświetlenia. Ustaw filtr dat.</p>
        @endif

        <div class="text-center">
            <a href="{{ route('raport') }}" class="inline-block bg-gray-500 text-white px-6 py-2 rounded font-medium">
                Powrót do raportów
            </a>
        </div>

    </div>
@endsection
