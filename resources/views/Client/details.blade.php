@extends('layouts.app')

@section('content')
    @auth
        <div class="container mx-auto p-4 max-w-6xl">

            <h1 class="text-3xl font-bold mb-6 text-center">Kartoteka klienta: {{ $client->name }}</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Dane podstawowe -->
                <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
                    <h2 class="text-xl font-semibold mb-4">Dane podstawowe</h2>
                    <p><strong>ID:</strong> {{ $client->id }}</p>
                    <p><strong>Imię i nazwisko:</strong> {{ $client->name }}</p>
                    <p><strong>Data urodzenia:</strong> {{ $client->date_of_birth?->format('Y-m-d') ?? '-' }}</p>
                    <p><strong>Płeć:</strong> {{ ucfirst($client->gender) ?? '-' }}</p>
                    <p><strong>Język:</strong> {{ $client->language ?? '-' }}</p>
                    <p><strong>Potrzeby mobilności:</strong> {{ $client->mobility_needs ?? '-' }}</p>
                </div>

                <!-- Kontakt i zgody -->
                <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
                    <h2 class="text-xl font-semibold mb-4">Kontakt i zgody</h2>
                    <p><strong>Email:</strong> {{ $client->email }}</p>
                    <p><strong>Telefon:</strong> {{ $client->phone }}</p>
                    <p><strong>Adres:</strong> {{ $client->address ?? '-' }}</p>
                    <p><strong>Kontakt awaryjny:</strong> {{ $client->emergency_contact ?? '-' }}</p>
                    <p><strong>Preferowany kontakt:</strong> {{ ucfirst($client->preferred_contact_method) }}</p>
                    <p><strong>Zgoda na przetwarzanie danych:</strong> {{ $client->consent ? 'Tak' : 'Nie' }}</p>
                </div>

                <!-- Status, problem i sprzęt -->
                <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
                    <h2 class="text-xl font-semibold mb-4">Status i informacje dodatkowe</h2>
                    <p><strong>Status:</strong> {{ ucfirst($client->status) }}</p>
                    <p><strong>Problem:</strong> {{ $client->problem ?? '-' }}</p>
                    <p><strong>Sprzęt:</strong> {{ $client->equipment ?? '-' }}</p>
                    <p><strong>Uwagi:</strong> {{ $client->notes ?? '-' }}</p>
                </div>
            </div>

            <!-- Dostępność dni i sloty godzinowe -->
            <div class="mt-6 bg-white shadow rounded-lg p-6 border border-gray-200">
                <h2 class="text-xl font-semibold mb-4">Dostępność</h2>
                @if($days_slots)
                    <table class="w-full text-sm border border-gray-300">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b">Dzień</th>
                            <th class="px-4 py-2 border-b">Sloty godzinowe</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($days_slots as $day => $slots)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2 border-b">{{ ucfirst($day) }}</td>
                                <td class="px-4 py-2 border-b">
                                    @foreach($slots as $slot)
                                        <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded mr-1 mb-1">{{ $slot }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">Brak danych o dostępności</p>
                @endif
            </div>

            <!-- Historia zmian i aktywność -->
            @if($client->activities->isNotEmpty())
                <div class="mt-6 bg-white shadow rounded-lg p-6 border border-gray-200">
                    <h2 class="text-xl font-semibold mb-4">Historia zmian</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium">Data</th>
                                <th class="px-4 py-2 text-left font-medium">Użytkownik</th>
                                <th class="px-4 py-2 text-left font-medium">Akcja</th>
                                <th class="px-4 py-2 text-left font-medium">Zmiany</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Powiązane terminarze -->
            @if($client->schedules->isNotEmpty())
                <div class="mt-6 bg-white shadow rounded-lg p-6 border border-gray-200">
                    <h2 class="text-xl font-semibold mb-4">Terminarz klienta</h2>
                    <ul class="list-disc pl-5 text-gray-800">
                        @foreach($client->schedules as $schedule)
                            <li>{{ $schedule->start_time->format('d.m.Y H:i') }} - {{ $schedule->status_label }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Przycisk powrotu -->
            <div class="mt-6 text-right">
                <a href="{{ route('clients.index') }}"
                   class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 font-medium transition">
                    ← Wróć do listy klientów
                </a>
            </div>
        </div>
    @else
        <div class="container mx-auto p-4 text-center">
            <p class="text-red-600 font-bold text-lg">Musisz być zalogowany, aby zobaczyć kartotekę klienta.</p>
        </div>
    @endauth
@endsection
