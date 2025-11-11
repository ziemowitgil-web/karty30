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
                    'sha1' => substr(sha1(file_get_contents($userCertPath)),0,10).'...',
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
                    <li><strong>SHA1 (skrót):</strong> <span class="font-mono">{{ $userCertData['sha1'] }}</span></li>
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

        {{-- Niepodpisane konsultacje --}}
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

        {{-- Podpisane konsultacje --}}
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

        {{-- Modale podpisu i historii --}}
        <div id="signModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg w-96">
                <h3 class="text-lg font-semibold mb-4">Podpis konsultacji</h3>
                <p id="signModalText" class="mb-4"></p>
                <div class="flex justify-end gap-2">
                    <button id="signCancel" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</button>
                    <button id="signConfirm" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Podpisz</button>
                </div>
            </div>
        </div>

        <div id="historyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg w-96 max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-semibold mb-4">Historia konsultacji</h3>
                <ul id="historyList" class="text-sm text-gray-700 space-y-1"></ul>
                <div class="flex justify-end mt-4">
                    <button id="historyClose" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Zamknij</button>
                </div>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- Podpis ---
            const signModal = document.getElementById('signModal');
            const signModalText = document.getElementById('signModalText');
            let signId = null;

            document.querySelectorAll('.sign-button').forEach(btn => {
                btn.addEventListener('click', () => {
                    signId = btn.dataset.id;
                    signModalText.textContent = `Czy chcesz podpisać konsultację #${signId}?`;
                    signModal.classList.remove('hidden');
                });
            });

            document.getElementById('signCancel').addEventListener('click', () => {
                signId = null;
                signModal.classList.add('hidden');
            });

            document.getElementById('signConfirm').addEventListener('click', () => {
                if(!signId) return;
                fetch(`/consultations/${signId}/sign`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(res => res.json())
                    .then(data => {
                        if(data.success){
                            location.reload();
                        } else {
                            alert('Błąd podpisu: ' + data.message);
                        }
                    });
            });

            // --- Historia ---
            const historyModal = document.getElementById('historyModal');
            const historyList = document.getElementById('historyList');

            document.querySelectorAll('.history-button').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    historyList.innerHTML = '<li>Ładowanie...</li>';
                    historyModal.classList.remove('hidden');

                    fetch(`/consultations/${id}/history`, {
                        headers: { 'Accept': 'application/json' }
                    }).then(res => res.json())
                        .then(data => {
                            if(data.history){
                                historyList.innerHTML = '';
                                data.history.forEach(h => {
                                    const li = document.createElement('li');
                                    li.textContent = `${h.created_at}: ${h.action}`;
                                    historyList.appendChild(li);
                                });
                            } else {
                                historyList.innerHTML = '<li>Brak historii</li>';
                            }
                        });
                });
            });

            document.getElementById('historyClose').addEventListener('click', () => {
                historyModal.classList.add('hidden');
                historyList.innerHTML = '';
            });
        });
    </script>
@endsection
