@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">

        <h1 class="text-3xl font-bold mb-6">Konsultacje</h1>

        {{-- Środowisko staging --}}
        @if(app()->environment('staging'))
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                Uwaga: Certyfikat testowy systemu jest aktywny (STAGING)
            </div>

            <div class="mb-6 flex gap-4">
                <form method="POST" action="{{ route('consultations.deleteTestData') }}">
                    @csrf
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-400">
                        Usuń dane testowe
                    </button>
                </form>
            </div>
        @endif

        <a href="{{ route('consultations.create') }}"
           class="bg-green-800 text-white px-4 py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-3 focus:ring-offset-2 focus:ring-blue-400 mb-6 inline-block">
            Nowa karta konsultacyjna
        </a>

        {{-- Niepodpisane --}}
        <h2 class="text-xl font-semibold mt-6 mb-2">Niepodpisane</h2>
        <div class="overflow-x-auto shadow rounded-lg border border-gray-200 mb-6">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Klient</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Data i godzina</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Czas trwania</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Akcje</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($consultations->where('status','draft') as $c)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2">{{ $c->id }}</td>
                        <td class="px-4 py-2">{{ $c->client->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($c->consultation_datetime)->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $c->duration_minutes }} min</td>
                        <td class="px-4 py-2 flex flex-wrap gap-2">
                            <a href="{{ route('consultations.details', $c) }}"
                               class="bg-gray-700 text-white px-3 py-1 rounded hover:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400">
                                Szczegóły
                            </a>
                            <button class="sign-button bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-400"
                                    data-id="{{ $c->id }}" aria-label="Podpisz konsultację {{ $c->id }}">
                                Podpisz
                            </button>
                            <button class="history-button bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400"
                                    data-id="{{ $c->id }}" aria-label="Historia konsultacji {{ $c->id }}">
                                Historia
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Podpisane --}}
        <h2 class="text-xl font-semibold mt-6 mb-2">Podpisane</h2>
        <div class="overflow-x-auto shadow rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Klient</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Data i godzina</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Czas trwania</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Odcisk palca (SHA1)</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Akcje</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($consultations->where('status','completed') as $c)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2">{{ $c->id }}</td>
                        <td class="px-4 py-2">{{ $c->client->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($c->consultation_datetime)->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $c->duration_minutes }} min</td>
                        <td class="px-4 py-2 font-mono">{{ $c->sha1sum ?? '-' }}</td>
                        <td class="px-4 py-2 flex flex-wrap gap-2">
                            <a href="{{ route('consultations.details', $c) }}"
                               class="bg-gray-700 text-white px-3 py-1 rounded hover:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400">
                                Szczegóły
                            </a>
                            <button class="history-button bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400"
                                    data-id="{{ $c->id }}" aria-label="Historia konsultacji {{ $c->id }}">
                                Historia
                            </button>
                            <a href="{{ route('consultations.pdf', $c) }}" target="_blank"
                               class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-400"
                               aria-label="Drukuj konsultację {{ $c->id }}">
                                Drukuj
                            </a>
                            <a href="{{ route('consultations.xml', $c) }}" target="_blank"
                               class="bg-purple-500 text-white px-3 py-1 rounded hover:bg-purple-600 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-purple-400"
                               aria-label="Podgląd XML konsultacji {{ $c->id }}">
                                Podgląd XML
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Tutaj wklej całą sekcję modali i JS z Twojego oryginalnego widoku -->

    </div>
@endsection
