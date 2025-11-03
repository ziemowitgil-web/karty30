@extends('layouts.quickreserviation')

@section('content')
    <div class="container mx-auto p-6 space-y-6">

        @if(session('quick_reserve_access') ?? false)

            <!-- Nagłówek z szybkim dodaniem klienta -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Szybka rezerwacja</h1>
                <button onclick="openAddClientModal()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded focus:ring focus:ring-green-300">
                    Dodaj nowego klienta
                </button>
            </div>

            <!-- Formularz szybkiej rezerwacji -->
            <div class="bg-white shadow rounded-lg p-4 border border-gray-200 space-y-6">
                <form method="POST" action="{{ route('quickreservationstore') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="client_id" class="block text-sm font-medium text-gray-700">Klient</label>
                            <input type="text" id="clientSearch" placeholder="Szukaj..."
                                   class="w-full border-2 border-gray-300 rounded p-2 focus:ring focus:ring-blue-300 mb-2">
                            <select name="client_id" id="client_id"
                                    class="w-full border-2 border-gray-300 rounded p-2 focus:ring focus:ring-blue-300">
                                <option value="">-- Wybierz --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" data-name="{{ $client->name }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">Data</label>
                            <input type="date" name="date" id="date"
                                   class="w-full border-2 border-gray-300 rounded p-2 focus:ring focus:ring-blue-300">
                        </div>
                        <div>
                            <label for="time" class="block text-sm font-medium text-gray-700">Godzina</label>
                            <input type="time" name="time" id="time"
                                   class="w-full border-2 border-gray-300 rounded p-2 focus:ring focus:ring-blue-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Czas trwania (min)</label>
                            <input type="number" name="duration_minutes" id="duration_minutes" min="1" value="60"
                                   class="w-full border-2 border-gray-300 rounded p-2 focus:ring focus:ring-blue-300">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status"
                                    class="w-full border-2 border-gray-300 rounded p-2 focus:ring focus:ring-blue-300">
                                <option value="preliminary">Wstępny</option>
                                <option value="confirmed">Potwierdzony</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Notatka (opcjonalnie)</label>
                        <input type="text" name="description" id="description"
                               class="w-full border-2 border-gray-300 rounded p-2 focus:ring focus:ring-blue-300">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                            Zarezerwuj
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabela z klientami i rezerwacjami -->
            <div class="bg-white shadow rounded-lg border border-gray-200 overflow-x-auto mt-6">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-100 text-gray-700 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-2">Data</th>
                        <th class="px-4 py-2">Godzina</th>
                        <th class="px-4 py-2">Klient</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Telefon</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Dostępne godz.</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @foreach($upcomingSchedules as $schedule)
                        @php
                            $client = $schedule->client;
                            $availableHours = $client->getAvailableHoursNumberAttribute() - $client->used;
                            $statusColor = match($client->status) {
                                'aktywny' => 'bg-green-200 text-green-800',
                                'nieaktywny' => 'bg-red-200 text-red-800',
                                'oczekujący' => 'bg-yellow-200 text-yellow-800',
                                default => 'bg-gray-200 text-gray-700',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2">{{ $schedule->start_time->format('d.m.Y') }}</td>
                            <td class="px-4 py-2">{{ $schedule->start_time->format('H:i') }}</td>
                            <td class="px-4 py-2">
                                <button type="button" onclick="openClientModal('client-{{ $client->id }}')"
                                        class="text-blue-700 hover:underline focus:outline-none">
                                    {{ $client->name }}
                                </button>
                            </td>
                            <td class="px-4 py-2">{{ $client->email ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $client->phone ?? '—' }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusColor }}">{{ $client->status }}</span>
                            </td>
                            <td class="px-4 py-2">{{ $availableHours }}h</td>
                        </tr>

                        <!-- Ukryty div z pełnymi danymi klienta -->
                        <div id="client-{{ $client->id }}" class="hidden">
                            <p><strong>Imię i nazwisko:</strong> {{ $client->name }}</p>
                            <p><strong>Email:</strong> {{ $client->email ?? '—' }}</p>
                            <p><strong>Telefon:</strong> {{ $client->phone ?? '—' }}</p>
                            <p><strong>Status:</strong> {{ $client->status ?? '—' }}</p>
                            <p><strong>Data urodzenia:</strong> {{ optional($client->date_of_birth)->format('d.m.Y') ?? '—' }}</p>
                            <p><strong>Płeć:</strong> {{ $client->gender ?? '—' }}</p>
                            <p><strong>Adres:</strong> {{ $client->address ?? '—' }}</p>
                            <p><strong>Problem:</strong> {{ $client->problem ?? '—' }}</p>
                            <p><strong>Sprzęt:</strong> {{ $client->equipment ?? '—' }}</p>
                            <p><strong>Zgoda:</strong> {{ $client->consent ? 'Tak' : 'Nie' }}</p>
                            <p><strong>Preferowana forma kontaktu:</strong> {{ $client->preferred_contact_method ?? '—' }}</p>
                            <p><strong>Notatki:</strong> {{ $client->notes ?? '—' }}</p>
                            <p><strong>Dostępne dni:</strong> {{ implode(', ', $client->available_days ?? []) }}</p>
                            <p><strong>Dostępne sloty:</strong> {{ implode(', ', $client->time_slots ?? []) }}</p>
                            <p><strong>Dostępne godziny:</strong> {{ implode(', ', $client->available_hours ?? []) }}</p>
                        </div>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Modal klienta -->
            <div id="clientModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                <div class="bg-white rounded shadow-lg p-6 w-full max-w-lg relative overflow-y-auto max-h-[80vh]">
                    <button onclick="closeClientModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                    <h2 class="text-xl font-bold mb-4">Dane klienta</h2>
                    <div id="clientDetails" class="text-gray-700"></div>
                </div>
            </div>

            <!-- Modal dodawania klienta -->
            <div id="addClientModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                <div class="bg-white rounded shadow-lg p-6 w-full max-w-md relative overflow-y-auto max-h-[80vh]">
                    <button onclick="closeAddClientModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                    <h2 class="text-xl font-bold mb-4">Dodaj nowego klienta</h2>
                    <form method="POST" action="{{ route('clients.store') }}" class="space-y-4">
                        @csrf
                        <input type="text" name="name" placeholder="Imię i nazwisko" class="w-full border rounded p-2" required>
                        <input type="email" name="email" placeholder="Email" class="w-full border rounded p-2">
                        <input type="text" name="phone" placeholder="Telefon" class="w-full border rounded p-2">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded mt-2">Dodaj klienta</button>
                    </form>
                </div>
            </div>

        @else
            <!-- Modal z hasłem -->
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
                    <h2 class="text-xl font-bold mb-4">Hasło dostępu</h2>
                    <form method="POST" action="{{ route('quickreservation') }}" class="space-y-4">
                        @csrf
                        <input type="password" name="quick_reserve_password" placeholder="Wpisz hasło"
                               class="w-full border-2 border-red-500 rounded p-2 focus:ring focus:ring-red-300" required>
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                                Zatwierdź
                            </button>
                        </div>
                        @error('quick_reserve_password')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script>
        function openClientModal(clientId) {
            const modal = document.getElementById('clientModal');
            const details = document.getElementById('clientDetails');
            const clientDiv = document.getElementById(clientId);
            details.innerHTML = clientDiv ? clientDiv.innerHTML : '<p class="text-red-600">Brak danych klienta.</p>';
            modal.classList.remove('hidden');
        }
        function closeClientModal() {
            document.getElementById('clientModal').classList.add('hidden');
        }
        function openAddClientModal() {
            document.getElementById('addClientModal').classList.remove('hidden');
        }
        function closeAddClientModal() {
            document.getElementById('addClientModal').classList.add('hidden');
        }

        // Filtr klientów w selekcie
        document.getElementById('clientSearch')?.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#client_id option').forEach(opt => {
                opt.style.display = (opt.textContent.toLowerCase().includes(filter) || opt.value === '') ? 'block' : 'none';
            });
        });
    </script>
@endsection
