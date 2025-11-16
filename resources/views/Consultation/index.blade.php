@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-7xl">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-3xl font-bold text-gray-900">Lista konsultacji</h1>
            <a href="{{ route('consultations.create') }}"
               class="inline-block bg-blue-600 text-white font-semibold px-5 py-3 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
               role="button" aria-label="Dodaj nową konsultację">
                Nowa konsultacja
            </a>
        </div>

        {{-- Alerty --}}
        @if(session('success'))
            <div class="text-green-700 bg-green-100 p-3 mb-4 rounded" role="alert" aria-live="polite">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="text-red-700 bg-red-100 p-3 mb-4 rounded" role="alert" aria-live="polite">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filtry --}}
        <form id="filterForm" method="GET" class="mb-6 flex flex-wrap gap-4 items-end" role="search" aria-label="Filtruj konsultacje">
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
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                       class="w-full border rounded p-2">
            </div>

            <div class="flex-1 min-w-[150px]">
                <label for="date_to" class="block text-gray-700 font-medium mb-1">Do</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                       class="w-full border rounded p-2">
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
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                    Filtruj
                </button>
                <a href="{{ route('consultations.index') }}"
                   class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                    Reset
                </a>
            </div>
        </form>

        {{-- Tabela --}}
        <div class="overflow-x-auto rounded border border-gray-200 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200" role="table" aria-label="Lista konsultacji">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">ID</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Klient</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Prowadzący</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Data i godzina</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Czas trwania</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Tryb</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">SHA1</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Kolejna akcja</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Działania</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @php
                    $sorted = $consultations->sortByDesc(fn($c) => !$c->signed);
                @endphp
                @forelse($sorted as $c)
                    <tr class="hover:bg-gray-50 {{ !$c->signed ? 'bg-yellow-50' : '' }}">
                        <td class="px-4 py-2">{{ $c->id }}</td>
                        <td class="px-4 py-2">{{ $c->client->name ?? 'SYSTEM' }}</td>
                        <td class="px-4 py-2">{{ $c->user->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($c->consultation_datetime)->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-2">{{ intdiv($c->duration_minutes, 60) }}h {{ $c->duration_minutes % 60 }}m</td>
                        <td class="px-4 py-2 capitalize">{{ $c->mode }}</td>
                        <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-white text-xs font-semibold
                        @if($c->status==='draft') bg-yellow-500
                        @elseif($c->status==='pending_system') bg-blue-500
                        @elseif($c->status==='pending_signature') bg-purple-500
                        @elseif($c->status==='completed') bg-green-500 @endif">
                            {{ ucfirst($c->status) }}
                        </span>
                        </td>
                        <td class="px-4 py-2 font-mono text-sm truncate max-w-[120px]">{{ $c->sha1sum ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $c->next_action ?? '-' }}</td>
                        <td class="px-4 py-2 flex gap-2">
                            <a href="{{ route('consultations.details', $c) }}"
                               class="text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 rounded">
                                Podgląd
                            </a>
                            @if(!$c->signed)
                                <form action="{{ route('consultations.sign', $c) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-green-600 hover:underline focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 rounded">
                                        Podpisz
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-4 text-center text-gray-500">Brak konsultacji</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
