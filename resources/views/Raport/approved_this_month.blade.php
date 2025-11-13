@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-6xl">
        <h1 class="text-3xl font-extrabold mb-8 text-gray-800">
            Zatwierdzone konsultacje - {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
        </h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 border border-green-300 rounded-lg p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($consultations->isEmpty())
            <p class="text-gray-600 text-center">Brak zatwierdzonych konsultacji w tym miesiącu.</p>
        @else
            <div class="overflow-x-auto bg-white shadow-md rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Klient</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Data i godzina</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Czas trwania (min)</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Przeprowadził</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Opis</th>
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
                            <td class="px-4 py-3 text-gray-600">{{ Str::limit($c->description, 50) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-50 text-blue-700 rounded-xl shadow p-4 flex flex-col items-center justify-center">
                    <div class="text-3xl font-bold">{{ $total }}</div>
                    <div class="mt-1 font-medium">Łącznie zatwierdzonych konsultacji</div>
                </div>

                <div class="bg-red-50 text-red-700 rounded-xl shadow p-4 flex flex-col justify-center">
                <span class="font-medium text-sm">
                    Dane osoby zatwierdzającej nie są widoczne w raporcie z powodu ograniczenia systemowego.
                    Sprawdź <a href="{{route('consultations.index')}}" class="underline text-red-600 hover:text-red-800">pod tym linkiem</a>.
                </span>
                </div>
            </div>
        @endif
    </div>
@endsection
