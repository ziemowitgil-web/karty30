@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-6xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Lista konsultacji</h1>
            <a href="{{ route('consultations.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Nowa konsultacja
            </a>
        </div>

        {{-- Alert sukcesu --}}
        @if(session('success'))
            <div class="text-green-700 bg-green-100 p-3 mb-4 rounded" role="alert" aria-live="polite">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filtrowanie --}}
        <form id="filterForm" method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[150px]">
                <label for="client_filter" class="block text-gray-700 font-medium mb-1">Klient</label>
                <select id="client_filter" name="client_id" class="w-full border rounded p-2">
                    <option value="">— Wszyscy —</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[150px]">
                <label for="date_from" class="block text-gray-700 font-medium mb-1">Od</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded p-2">
            </div>

            <div class="flex-1 min-w-[150px]">
                <label for="date_to" class="block text-gray-700 font-medium mb-1">Do</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded p-2">
            </div>

            <div class="flex-1 min-w-[150px]">
                <label for="mode_filter" class="block text-gray-700 font-medium mb-1">Tryb</label>
                <select id="mode_filter" name="mode" class="w-full border rounded p-2">
                    <option value="">— Wszystkie —</option>
                    <option value="manual" {{ request('mode')=='manual'?'selected':'' }}>Bez rezerwacji</option>
                    <option value="reservation" {{ request('mode')=='reservation'?'selected':'' }}>Rezerwacja</option>
                    <option value="pfron" {{ request('mode')=='pfron'?'selected':'' }}>Szkolenie PFRON</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filtruj</button>
                <a href="{{ route('consultations.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Reset</a>
            </div>
        </form>

        {{-- Tabela konsultacji --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded overflow-hidden">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border-b text-left">ID</th>
                    <th class="px-4 py-2 border-b text-left">Klient</th>
                    <th class="px-4 py-2 border-b text-left">Data i godzina</th>
                    <th class="px-4 py-2 border-b text-left">Czas trwania</th>
                    <th class="px-4 py-2 border-b text-left">Tryb</th>
                    <th class="px-4 py-2 border-b text-left">Działania</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @php
                    // Sortuj: niepodpisane na górze
                    $sorted = $consultations->sortByDesc(fn($c) => !$c->signed);
                @endphp

                @forelse($sorted as $c)
                    <tr class="hover:bg-gray-50 {{ !$c->signed ? 'bg-yellow-50' : '' }}">
                        <td class="px-4 py-2">{{ $c->id }}</td>
                        <td class="px-4 py-2">{{ $c->client->name ?? 'SYSTEM' }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($c->consultation_date)->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-2">
                            {{ intdiv($c->duration_minutes, 60) }}h {{ $c->duration_minutes % 60 }}m
                        </td>
                        <td class="px-4 py-2 capitalize">{{ $c->mode }}</td>
                        <td class="px-4 py-2 flex gap-2">
                            <a href="{{ route('consultations.details', $c) }}" class="text-blue-600 hover:underline">Podgląd</a>
                            @if(!$c->signed)
                                <form action="{{ route('consultations.sign', $c) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:underline">Podpisz</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">Brak konsultacji</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
