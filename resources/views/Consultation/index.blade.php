@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Konsultacje</h1>

        {{-- Krótkie info o certyfikacie --}}
        @php
            $certPath = storage_path("app/certificates/".Auth::user()->id."_user_cert.pem");
            $certActive = file_exists($certPath);
            $certCN = $certActive ? openssl_x509_parse(openssl_x509_read(file_get_contents($certPath)))['subject']['CN'] ?? '-' : null;
        @endphp

        <div class="{{ $certActive ? 'border-l-4 border-blue-500 bg-blue-50 text-blue-700' : 'border-l-4 border-red-500 bg-red-50 text-red-700' }} p-3 rounded mb-4">
            @if($certActive)
                Certyfikat aktywny: <strong>{{ $certCN }}</strong>
            @else
                Brak certyfikatu – podpisz dokumenty nieaktywny
            @endif
        </div>

        <a href="{{ route('consultations.create') }}"
           class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-400 mb-6 inline-block">
            Nowa konsultacja
        </a>

        {{-- Tabela konsultacji --}}
        <h2 class="text-xl font-semibold mt-6 mb-2">Lista konsultacji</h2>
        <div class="overflow-x-auto shadow rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">ID</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Klient</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Tryb</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Data i godzina</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Czas</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">SHA1</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Akcje</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($consultations as $c)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2">{{ $c->id }}</td>
                        <td class="px-4 py-2">{{ $c->client->name ?? '-' }}</td>
                        <td class="px-4 py-2">
                            @switch($c->mode)
                                @case('reservation') Z rezerwacji @break
                                @case('manual') Manualna @break
                                @case('pfron') PFRON @break
                                @default -
                            @endswitch
                        </td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($c->consultation_datetime)->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-2">{{ intdiv($c->duration_minutes,60) }}h {{ $c->duration_minutes % 60 }}m</td>
                        <td class="px-4 py-2 font-mono">{{ $c->sha1sum ?? '-' }}</td>
                        <td class="px-4 py-2 flex flex-wrap gap-2">
                            @if(!$c->sha1sum)
                                <button class="sign-button bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-400"
                                        data-id="{{ $c->id }}"
                                        {{ $certActive ? '' : 'disabled' }}
                                        aria-label="Podpisz konsultację #{{ $c->id }}">
                                    Podpisz
                                </button>
                            @endif
                            <button class="history-button bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400"
                                    data-id="{{ $c->id }}"
                                    aria-label="Historia konsultacji #{{ $c->id }}">
                                Historia
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Modale --}}
        <div id="ariaMessage" class="sr-only" aria-live="polite"></div>

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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ariaMessage = document.getElementById('ariaMessage');

            // --- Podpis w AJAX ---
            const signModal = document.getElementById('signModal');
            const signModalText = document.getElementById('signModalText');
            let signId = null;

            document.querySelectorAll('.sign-button').forEach(btn => {
                btn.addEventListener('click', () => {
                    signId = btn.dataset.id;
                    signModalText.textContent = `Czy chcesz podpisać konsultację #${signId}?`;
                    signModal.classList.remove('hidden');
                    ariaMessage.textContent = `Otworzono modal podpisu konsultacji #${signId}`;
                });
            });

            document.getElementById('signCancel').addEventListener('click', () => {
                signId = null;
                signModal.classList.add('hidden');
                ariaMessage.textContent = `Anulowano podpis konsultacji`;
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
                            const row = document.querySelector(`button.sign-button[data-id='${signId}']`).closest('tr');
                            row.querySelector('td:nth-child(6)').textContent = data.sha1sum;
                            row.querySelector('.sign-button')?.remove();
                            signModal.classList.add('hidden');
                            ariaMessage.textContent = `Konsultacja #${signId} podpisana`;
                        } else {
                            alert('Błąd podpisu: ' + data.message);
                            ariaMessage.textContent = `Błąd podpisu konsultacji #${signId}`;
                        }
                    });
            });

            // --- Historia w AJAX ---
            const historyModal = document.getElementById('historyModal');
            const historyList = document.getElementById('historyList');

            document.querySelectorAll('.history-button').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    historyList.innerHTML = '<li>Ładowanie...</li>';
                    historyModal.classList.remove('hidden');
                    ariaMessage.textContent = `Ładowanie historii konsultacji #${id}`;

                    fetch(`/consultations/${id}/history`, {
                        headers: { 'Accept': 'application/json' }
                    }).then(res => res.json())
                        .then(data => {
                            historyList.innerHTML = '';
                            if(data.history && data.history.length > 0){
                                data.history.forEach(h => {
                                    const li = document.createElement('li');
                                    li.textContent = `${h.created_at}: ${h.action}`;
                                    historyList.appendChild(li);
                                });
                            } else {
                                historyList.innerHTML = '<li>Brak historii</li>';
                            }
                            ariaMessage.textContent = `Historia konsultacji #${id} załadowana`;
                        });
                });
            });

            document.getElementById('historyClose').addEventListener('click', () => {
                historyModal.classList.add('hidden');
                historyList.innerHTML = '';
                ariaMessage.textContent = `Zamknięto modal historii konsultacji`;
            });
        });
    </script>
@endsection
