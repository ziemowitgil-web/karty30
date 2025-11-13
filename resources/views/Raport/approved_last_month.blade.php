@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-6xl">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-3xl font-extrabold text-gray-800">
                Zatwierdzone konsultacje - poprzedni miesiąc
            </h1>
            <a href="{{ route('raport') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-700 transition font-medium">
                Powrót do raportów
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-blue-50 text-blue-700 p-6 rounded-xl shadow flex flex-col items-center justify-center">
                <div class="text-3xl font-bold">{{ $total }}</div>
                <div class="mt-1 font-medium text-center">Łącznie zatwierdzonych konsultacji</div>
            </div>
            <div class="bg-gray-50 text-gray-700 p-6 rounded-xl shadow flex flex-col items-center justify-center">
                <div class="text-lg">
                    Miesiąc: <strong>{{ \Carbon\Carbon::createFromDate($year, $month)->format('F Y') }}</strong>
                </div>
            </div>
        </div>

        @if($consultations->isEmpty())
            <p class="text-gray-600 text-center">Brak zatwierdzonych konsultacji w poprzednim miesiącu.</p>
        @else
            <div class="overflow-x-auto bg-white shadow-md rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">ID</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Klient</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Data i godzina</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Czas trwania (min)</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Przeprowadził</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Akcje</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($consultations as $c)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-gray-700">{{ $c->id }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $c->client->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ \Carbon\Carbon::parse($c->consultation_datetime)->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $c->duration_minutes }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $c->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 space-x-2">
                                <a href="{{ route('consultations.print', $c) }}" target="_blank"
                                   class="bg-green-500 text-white px-3 py-1 rounded-lg hover:bg-green-600 text-sm font-medium transition">
                                    Drukuj
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
