@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-2xl font-bold mb-6">Raport odwołań </h1>

        <!-- Filtr dat -->
        <form action="{{ route('raports.cancelled') }}" method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label for="from" class="block text-gray-700 font-medium mb-1">Od:</label>
                <input type="date" name="from" id="from" value="{{ old('from', $from ?? '') }}"
                       class="border border-gray-300 rounded px-3 py-2">
            </div>
            <div>
                <label for="to" class="block text-gray-700 font-medium mb-1">Do:</label>
                <input type="date" name="to" id="to" value="{{ old('to', $to ?? '') }}"
                       class="border border-gray-300 rounded px-3 py-2">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    Filtruj
                </button>
            </div>
        </form>

        @if(isset($total))
            <div class="bg-white shadow rounded border border-gray-200 p-4">
                <h2 class="text-xl font-semibold mb-4">Podsumowanie</h2>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold">Typ odwołania</th>
                        <th class="px-4 py-2 text-left font-semibold">Liczba</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-4 py-2">Odwołane przez FEER</td>
                        <td class="px-4 py-2">{{ $cancelled_by_feer }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2">Odwołane przez Beneficjenta</td>
                        <td class="px-4 py-2">{{ $cancelled_by_client }}</td>
                    </tr>
                    <tr class="font-semibold">
                        <td class="px-4 py-2">Łącznie</td>
                        <td class="px-4 py-2">{{ $total }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">Brak danych do wyświetlenia. Ustaw filtr dat.</p>
        @endif
    </div>
@endsection
