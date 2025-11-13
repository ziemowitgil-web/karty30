@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">

        <!-- Nagłówek strony -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <i class="fas fa-users text-blue-600"></i> Lista klientów
            </h1>
            <a href="{{ route('clients.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 flex items-center gap-1">
                <i class="fas fa-plus"></i> Dodaj klienta
            </a>
        </div>

        <!-- Wiadomości sukcesu -->
        @if(session('success'))
            <div class="bg-green-100 text-green-800 border border-green-300 rounded p-3 mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Brak klientów -->
        @if($clients->isEmpty())
            <p class="text-gray-600">Brak klientów.</p>
        @else
            <!-- Tabela klientów -->
            <div class="overflow-x-auto bg-white shadow rounded border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold">ID</th>
                        <th class="px-4 py-2 text-left font-semibold">Imię i nazwisko</th>
                        <th class="px-4 py-2 text-left font-semibold">Email</th>
                        <th class="px-4 py-2 text-left font-semibold">Telefon</th>
                        <th class="px-4 py-2 text-center font-semibold">Status</th>
                        <th class="px-4 py-2 text-center font-semibold">Limit</th>
                        <th class="px-4 py-2 text-center font-semibold">Wykorzystane</th>
                        <th class="px-4 py-2 text-center font-semibold">Czarna lista</th>
                        <th class="px-4 py-2 text-center font-semibold">Akcje</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @foreach($clients as $client)
                        @php
                            // Sprawdzenie czarnej listy
                            $blacklisted = \App\Models\ClientBlacklist::where('name', $client->name)->first();

                            // Klasy i etykiety statusu
                            $statusClasses = [
                                'enrolled' => 'bg-indigo-200 text-indigo-800',
                                'ready' => 'bg-teal-200 text-teal-800',
                                'to_settle' => 'bg-orange-200 text-orange-800',
                                'default' => 'bg-gray-100 text-gray-700',
                            ];
                            $statusLabels = [
                                'enrolled' => 'W bazie',
                                'ready' => 'Skorzystał',
                                'to_settle' => 'Do rozliczenia',
                                'default' => 'Inne',
                            ];
                            $badgeClass = $statusClasses[$client->status] ?? $statusClasses['default'];
                            $badgeLabel = $statusLabels[$client->status] ?? $statusLabels['default'];

                            // Preferowane godziny - bezpieczne json_decode
                            $available_hours = [];
                            if ($client->available_days) {
                                if (is_array($client->available_days)) {
                                    $available_hours = $client->available_days;
                                } else {
                                    $available_hours = json_decode($client->available_days, true) ?? [];
                                }
                            }
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $client->id }}</td>
                            <td class="px-4 py-2">{{ $client->name }}</td>
                            <td class="px-4 py-2">{{ $client->email ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $client->phone ?? '—' }}</td>
                            <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                {{ $badgeLabel }}
                            </span>
                            </td>
                            <td class="px-4 py-2 text-center">{{ $client->limit ?? 3 }}</td>
                            <td class="px-4 py-2 text-center">{{ $client->used ?? 0 }}</td>

                                
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if($blacklisted)
                                    <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-semibold">
                                    {{ $blacklisted->reason }}
                                </span>
                                @else
                                    <span class="text-gray-400 text-xs">Nie ma CL</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 flex flex-wrap justify-center gap-1">
                                <a href="{{ route('clients.details', $client->id) }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 text-sm">
                                    <i class="fas fa-eye"></i> Szczegóły
                                </a>
                                <a href="{{ route('clients.print', $client->id) }}" target="_blank" class="text-green-600 hover:text-green-800 flex items-center gap-1 text-sm">
                                    <i class="fas fa-file-pdf"></i> PDF RODO
                                </a>

                                @if($blacklisted)
                                    <form action="{{ route('schedules.client_blacklist.destroy', $blacklisted->id) }}" method="POST" class="inline" onsubmit="return confirm('Na pewno chcesz usunąć klienta z czarnej listy?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-700 hover:text-gray-900 flex items-center gap-1 text-sm">
                                            <i class="fas fa-times-circle"></i> Usuń z CL
                                        </button>
                                    </form>
                                @else
                                    <button type="button" onclick="openBlacklistModal('{{ $client->name }}')" class="text-black hover:text-gray-800 flex items-center gap-1 text-sm">
                                        <i class="fas fa-ban"></i> Dodaj do CL
                                    </button>
                                @endif

                                @if($client->status != 'to_settle')
                                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST" class="inline" onsubmit="return confirm('Na pewno chcesz usunąć klienta?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 flex items-center gap-1 text-sm">
                                            <i class="fas fa-trash"></i> Usuń
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Modal dodawania do czarnej listy -->
        <div id="blacklistModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="fas fa-ban text-red-600"></i> Dodaj klienta do czarnej listy</h2>
                <form id="blacklistForm" method="POST" action="{{ route('schedules.client_blacklist.store') }}">
                    @csrf
                    <input type="hidden" name="name" id="blacklistName">
                    <div class="mb-4">
                        <label for="reason" class="block text-gray-700 font-medium mb-1">Powód</label>
                        <textarea name="reason" id="reason" rows="3" required class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeBlacklistModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-colors">Anuluj</button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800 transition-colors">Dodaj</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function openBlacklistModal(name) {
            document.getElementById('blacklistName').value = name;
            document.getElementById('reason').value = '';
            document.getElementById('blacklistModal').classList.remove('hidden');
            document.getElementById('blacklistModal').classList.add('flex');
        }
        function closeBlacklistModal() {
            document.getElementById('blacklistModal').classList.add('hidden');
            document.getElementById('blacklistModal').classList.remove('flex');
        }
    </script>
@endsection
