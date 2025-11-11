@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Konsultacje</h1>

        {{-- Środowisko staging --}}
        @if(app()->environment('staging'))
            <div class="border-2 border-yellow-400 bg-yellow-50 p-4 rounded-lg mb-6 flex items-start gap-4">
                <div>
                    <p class="text-yellow-800 font-semibold mb-2">Uwaga: Certyfikat testowy systemu jest aktywny (STAGING)</p>
                    <form method="POST" action="{{ route('consultations.deleteTestData') }}">
                        @csrf
                        <button type="submit"
                                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-400">
                            Usuń dane testowe
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Informacje o certyfikacie użytkownika --}}
        @php
            $userCertPath = storage_path("app/certificates/".Auth::user()->id."_user_cert.pem");
            $userCertData = null;
            if (file_exists($userCertPath)) {
                $cert = openssl_x509_read(file_get_contents($userCertPath));
                $parsed = openssl_x509_parse($cert);
                $userCertData = [
                    'CN' => $parsed['subject']['CN'] ?? '-',
                    'email' => $parsed['subject']['emailAddress'] ?? '-',
                    'O' => $parsed['subject']['O'] ?? '-',
                    'OU' => $parsed['subject']['OU'] ?? '-',
                    'valid_from' => isset($parsed['validFrom_time_t']) ? date('Y-m-d H:i:s', $parsed['validFrom_time_t']) : '-',
                    'valid_to' => isset($parsed['validTo_time_t']) ? date('Y-m-d H:i:s', $parsed['validTo_time_t']) : '-',
                    'sha1' => sha1(file_get_contents($userCertPath)),
                ];
            }
        @endphp

        @if($userCertData)
            <div class="border-l-4 border-blue-500 bg-blue-50 p-4 mb-6 rounded-lg">
                <h3 class="text-lg font-semibold mb-2 text-blue-700">Certyfikat użytkownika aktywny</h3>
                <ul class="text-gray-700 text-sm space-y-1">
                    <li><strong>Common Name (CN):</strong> {{ $userCertData['CN'] }}</li>
                    <li><strong>Email:</strong> {{ $userCertData['email'] }}</li>
                    <li><strong>Organizacja (O):</strong> {{ $userCertData['O'] }}</li>
                    <li><strong>Jednostka organizacyjna (OU):</strong> {{ $userCertData['OU'] }}</li>
                    <li><strong>Ważny od:</strong> {{ $userCertData['valid_from'] }}</li>
                    <li><strong>Ważny do:</strong> {{ $userCertData['valid_to'] }}</li>
                    <li><strong>SHA1:</strong> <span class="font-mono">{{ $userCertData['sha1'] }}</span></li>
                </ul>
                <p class="text-xs text-gray-500 mt-2">Jeśli nie jesteś pewna, nie podpisuj dokumentów.</p>
            </div>
        @else
            <div class="border-l-4 border-red-500 bg-red-50 p-4 mb-6 rounded-lg">
                <p class="text-red-700 font-semibold">Brak aktywnego certyfikatu użytkownika! Podpisywanie jest zablokowane.</p>
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
                            <button class="sign-button bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-400"
                                    data-id="{{ $c->id }}"
                                {{ $userCertData ? '' : 'disabled' }}>
                                Podpisz
                            </button>
                            <button class="history-button bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400"
                                    data-id="{{ $c->id }}">
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
                            <button class="history-button bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400"
                                    data-id="{{ $c->id }}">
                                Historia
                            </button>
                            <a href="{{ route('consultations.pdf', $c) }}" target="_blank"
                               class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm">
                                Drukuj
                            </a>
                            <a href="{{ route('consultations.xml', $c) }}" target="_blank"
                               class="bg-purple-500 text-white px-3 py-1 rounded hover:bg-purple-600 text-sm">
                                Podgląd XML
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Modale podpisu i historii pozostają bez zmian --}}
        @include('Consultation.partials.signModal')
        @include('Consultation.partials.historyModal')
    </div>
@endsection
