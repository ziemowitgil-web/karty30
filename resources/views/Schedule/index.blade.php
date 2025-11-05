@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <!-- Nagłówek -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Terminarz konsultacji</h1>
                <p class="text-gray-600 text-sm mt-1">Zarządzaj zaplanowanymi konsultacjami w systemie TyfloKonsultacje.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('schedules.create') }}"
                   class="inline-flex items-center bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Nowa rezerwacja
                </a>
            </div>
        </div>

        <!-- Komunikaty -->
        @if(session('success'))
            <div class="bg-green-100 text-green-800 border border-green-300 rounded-lg p-3 mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <!-- Brak konsultacji -->
        @if($schedules->isEmpty())
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg">
                Brak zaplanowanych konsultacji.
            </div>
        @else
            <!-- Tabela -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg border border-gray-200">
                <table class="min-w-full text-sm divide-y divide-gray-200" role="table">
                    <thead class="bg-gray-100 text-gray-700 uppercase tracking-wider text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Data</th>
                        <th class="px-4 py-3 text-left font-semibold">Godzina</th>
                        <th class="px-4 py-3 text-left font-semibold">Beneficjent</th>
                        <th class="px-4 py-3 text-left font-semibold">Czas trwania</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Notatka</th>
                        <th class="px-4 py-3 text-left font-semibold">Akcje</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @foreach($schedules as $schedule)
                        <tr class="hover:bg-gray-50 focus-within:bg-gray-50 transition">
                            <td class="px-4 py-3">{{ $schedule->start_time->format('d.m.Y') }}</td>
                            <td class="px-4 py-3">{{ $schedule->start_time->format('H:i') }} – {{ optional($schedule->end_time)->format('H:i') }}</td>
                            <td class="px-4 py-3">
                                <button type="button"
                                        class="text-blue-700 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-400 rounded"
                                        onclick="openClientModal('client-{{ $schedule->id }}')"
                                        aria-label="Zobacz dane beneficjenta: {{ $schedule->client->name }}">
                                    {{ $schedule->client->name ?? '—' }}
                                </button>
                                <div id="client-{{ $schedule->id }}" class="hidden" aria-hidden="true">
                                    <p><strong>Imię i nazwisko:</strong> {{ $schedule->client->name ?? '—' }}</p>
                                    <p><strong>Email:</strong> {{ $schedule->client->email ?? '—' }}</p>
                                    <p><strong>Telefon:</strong> {{ $schedule->client->phone ?? '—' }}</p>
                                    <p><strong>Status:</strong> {{ $schedule->client->status ?? '—' }}</p>
                                    <p><strong>Uwagi:</strong> {{ $schedule->client->notes ?? '—' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $schedule->duration_minutes }} min</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    @switch($schedule->status)
                                        @case('confirmed') bg-green-200 text-green-900 @break
                                        @case('preliminary') bg-yellow-200 text-yellow-900 @break
                                        @case('cancelled_by_feer') bg-orange-200 text-orange-900 @break
                                        @case('cancelled_by_client') bg-pink-200 text-pink-900 @break
                                        @case('attended') bg-teal-200 text-teal-900 @break
                                        @default bg-gray-200 text-gray-800
                                    @endswitch">
                                    {{ $schedule->status_label ?? ucfirst($schedule->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ Str::limit($schedule->description, 40) }}</td>

                            <!-- Akcje -->
                            <td class="px-4 py-3 flex flex-col gap-1">
                                <!-- Zmień termin -->
                                <a href="{{ route('schedules.rescheduleForm', $schedule) }}" class="text-indigo-700 hover:underline text-sm">Zmień termin</a>

                                <!-- Usuń -->
                                <form action="{{ route('schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć ten termin?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-700 hover:underline text-sm">Usuń</button>
                                </form>

                                <!-- Potwierdzenie wstępne -->
                                @if($schedule->status === 'preliminary')
                                    <form action="{{ route('schedules.markAttendance', $schedule) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-blue-700 hover:underline text-sm"
                                                onclick="return confirm('Potwierdzenie spowoduje wygenerowanie dokumentów. Kontynuować?')">
                                            Potwierdź termin
                                        </button>
                                    </form>
                                @endif

                                <!-- Oznacz jako obecność -->
                                @if($schedule->status !== 'attended')
                                    <form action="{{ route('schedules.markAttendance', $schedule) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-green-700 hover:underline text-sm">Obecność</button>
                                    </form>
                                @endif

                                <!-- Anulowanie -->
                                @if(in_array($schedule->status, ['preliminary','confirmed']))
                                    <form action="{{ route('schedules.cancelByFeer', $schedule) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="reason" value="Odwołane przez FEER">
                                        <button type="submit" class="text-orange-700 hover:underline text-sm">Odwołaj jako FEER</button>
                                    </form>
                                    <form action="{{ route('schedules.cancelByClient', $schedule) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="reason" value="Odwołane przez Beneficjenta">
                                        <button type="submit" class="text-pink-700 hover:underline text-sm">Odwołaj przez Beneficjenta</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginacja -->
            <div class="mt-6">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Beneficjenta -->
    <div id="clientModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" role="dialog" aria-modal="true">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md relative">
            <button onclick="closeClientModal()" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-2xl" aria-label="Zamknij okno">&times;</button>
            <h2 class="text-lg font-semibold mb-4 text-gray-800">Dane beneficjenta</h2>
            <div id="clientDetails" class="text-gray-700 text-sm leading-relaxed"></div>
        </div>
    </div>

    <script>
        function openClientModal(clientId) {
            const modal = document.getElementById('clientModal');
            const details = document.getElementById('clientDetails');
            const clientDiv = document.getElementById(clientId);
            details.innerHTML = clientDiv ? clientDiv.innerHTML : '<p class="text-red-600">Brak danych beneficjenta.</p>';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeClientModal() {
            const modal = document.getElementById('clientModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endsection
