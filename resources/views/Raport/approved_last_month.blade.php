@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Zatwierdzone konsultacje - poprzedni miesiąc</h1>
        <a href="{{ route('raport') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Powrót do raportów
        </a>
    </div>

    <p class="mb-4 text-gray-700">
        Miesiąc: <strong>{{ \Carbon\Carbon::createFromDate($year, $month)->format('F Y') }}</strong><br>
        Liczba zatwierdzonych konsultacji: <strong>{{ $total }}</strong>
    </p>

    @if($consultations->isEmpty())
    <p class="text-gray-600">Brak zatwierdzonych konsultacji w poprzednim miesiącu.</p>
    @else
    <div class="overflow-x-auto bg-white shadow rounded border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100 sticky top-0">
            <tr>
                <th class="px-4 py-2 text-left font-medium text-gray-700">ID</th>
                <th class="px-4 py-2 text-left font-medium text-gray-700">Klient</th>
                <th class="px-4 py-2 text-left font-medium text-gray-700">Data i godzina</th>
                <th class="px-4 py-2 text-left font-medium text-gray-700">Czas trwania (min)</th>
                <th class="px-4 py-2 text-left font-medium text-gray-700">Przeprowadził</th>
                <th class="px-4 py-2 text-left font-medium text-gray-700">Akcje</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($consultations as $c)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 text-gray-700">{{ $c->id }}</td>
                <td class="px-4 py-2 text-gray-700">{{ $c->client->name ?? '-' }}</td>
                <td class="px-4 py-2 text-gray-700">{{ \Carbon\Carbon::parse($c->consultation_datetime)->format('d.m.Y H:i') }}</td>
                <td class="px-4 py-2 text-gray-700">{{ $c->duration_minutes }}</td>
                <td class="px-4 py-2 text-gray-700">{{ $c->user->name ?? '-' }}</td>
                <td class="px-4 py-2 space-x-2">
                    <a href="{{ route('consultations.print', $c) }}" target="_blank"
                       class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-sm">
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
