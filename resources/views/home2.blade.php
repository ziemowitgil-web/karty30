@extends('layouts.app')

@section('content')
    <main class="max-w-6xl mx-auto p-6 space-y-8" role="main">
        <header>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Panel użytkownika</h1>
            <p class="text-gray-600">Witaj, {{ auth()->user()->name }}. Twoje podsumowanie aktywności.</p>
        </header>

        <!-- Statystyki -->
        <section aria-labelledby="stats-heading" class="space-y-2">
            <h2 id="stats-heading" class="text-xl font-semibold text-gray-800">Statystyki</h2>
            <ul class="list-disc list-inside">
                <li>Wersje robocze: <strong>{{ $stats['draft'] ?? 0 }}</strong></li>
                <li>Zatwierdzone: <strong>{{ $stats['completed'] ?? 0 }}</strong></li>
                <li>Anulowane: <strong>{{ $stats['cancelled'] ?? 0 }}</strong></li>
            </ul>
        </section>

        <!-- Ostatnie akcje -->
        <section aria-labelledby="actions-heading" class="space-y-2">
            <h2 id="actions-heading" class="text-xl font-semibold text-gray-800">Ostatnie akcje</h2>
            @if($recentActions->isEmpty())
                <p>Brak ostatnich akcji.</p>
            @else
                <ul class="list-disc list-inside space-y-1">
                    @foreach($recentActions as $action)
                        <li class="text-sm">
                            <span class="font-medium">{{ $action->action_type }}</span>,
                            {{ $action->created_at->format('d.m H:i') }},
                            {{ $action->target_name ?? '-' }},
                            status: {{ $action->status_label ?? '-' }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <!-- Dzisiejsze -->
        <section aria-labelledby="today-heading" class="overflow-x-auto">
            <h2 id="today-heading" class="text-xl font-semibold text-gray-800 mb-2">Zaplanowane na dziś</h2>
            @if($todaySchedules->isEmpty())
                <p>Brak zaplanowanych rezerwacji.</p>
            @else
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="text-left px-3 py-2 border">Godzina</th>
                        <th class="text-left px-3 py-2 border">Klient</th>
                        <th class="text-left px-3 py-2 border">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($todaySchedules as $schedule)
                        <tr class="odd:bg-white even:bg-gray-50">
                            <td class="px-3 py-2 border">{{ $schedule->start_time->format('H:i') }}</td>
                            <td class="px-3 py-2 border">{{ $schedule->client->name ?? '-' }}</td>
                            <td class="px-3 py-2 border">{{ $schedule->status_label }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <!-- Następne 7 dni -->
        <section aria-labelledby="week-heading" class="overflow-x-auto">
            <h2 id="week-heading" class="text-xl font-semibold text-gray-800 mb-2">Następne 7 dni</h2>
            @if($weekSchedules->isEmpty())
                <p>Brak zaplanowanych rezerwacji.</p>
            @else
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="text-left px-3 py-2 border">Data</th>
                        <th class="text-left px-3 py-2 border">Godzina</th>
                        <th class="text-left px-3 py-2 border">Klient</th>
                        <th class="text-left px-3 py-2 border">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($weekSchedules as $schedule)
                        <tr class="odd:bg-white even:bg-gray-50">
                            <td class="px-3 py-2 border">{{ $schedule->start_time->format('d.m.Y') }}</td>
                            <td class="px-3 py-2 border">{{ $schedule->start_time->format('H:i') }}</td>
                            <td class="px-3 py-2 border">{{ $schedule->client->name ?? '-' }}</td>
                            <td class="px-3 py-2 border">{{ $schedule->status_label }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </main>
@endsection
