@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">

        <h1 class="text-2xl font-bold mb-4">
            Zatwierdzone konsultacje - {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
        </h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 border border-green-300 rounded p-3 mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($consultations->isEmpty())
            <p class="text-gray-600">Brak zatwierdzonych konsultacji w tym miesiącu.</p>
        @else
            <div class="overflow-x-auto bg-white shadow rounded border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold">ID</th>
                        <th class="px-4 py-2 text-left font-semibold">Klient</th>
                        <th class="px-4 py-2 text-left font-semibold">Data i godzina</th>
                        <th class="px-4 py-2 text-left font-semibold">Czas trwania (min)</th>
                        <th class="px-4 py-2 text-left font-semibold">Przeprowadził</th>
                        <th class="px-4 py-2 text-left font-semibold">Opis</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($consultations as $c)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-700">{{ $c->id }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ $c->client->name ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-700">
                                {{ \Carbon\Carbon::parse($c->consultation_datetime)->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-4 py-2 text-gray-700">{{ $c->duration_minutes }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ $c->user->name ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ Str::limit($c->description, 50) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>

            <div class="mt-4">

                <span class="text-gray-700 font-medium">Łącznie zatwierdzonych konsultacji: {{ $total }}</span>
<br>
                <span class="text-red-600 font-medium"> Dane osoby zatwierdzającej nie są widoczne w raporcie z powodu ograniczenia systemowego,
                    sprawdź <a href="{{route('consultations.index')}}">pod tym linkiem</a>
                </span>


            </div>
        @endif

    </div>
@endsection
