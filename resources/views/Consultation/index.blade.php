@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">

        {{-- Komunikat o środowisku testowym --}}
        @if(env('APP_ENV') === 'staging')
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert" tabindex="0">
                Środowisko STAGING: aktywny jest <strong>certyfikat testowy systemu</strong>. Dokumenty podpisywane są tylko testowo.
            </div>
        @endif

        {{-- Komunikat o niepodpisanych konsultacjach --}}
        @php $draftCount = $consultations->where('status','draft')->count(); @endphp
        @if($draftCount > 0)
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert" tabindex="0">
                Masz {{ $draftCount }} niepodpisane konsultacje!
            </div>
        @endif

        <h1 class="text-3xl font-bold mb-6">Konsultacje</h1>

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
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Klient</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Data i godzina</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Czas trwania</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Akcje</th>
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
                            <button class="sign-button bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm"
                                    data-id="{{ $c->id }}">
                                Podpisz
                            </button>
                            <button class="history-button bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm"
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
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Klient</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Data i godzina</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Czas trwania</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Odcisk palca (fingerprint)</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Akcje</th>
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
                            <button class="history-button bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm"
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

        {{-- Modal podpisu --}}
        <div id="signModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-6 rounded-xl max-w-2xl w-full shadow-lg relative">
                <button id="closeSignModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800" aria-label="Zamknij modal">&times;</button>

                <h3 class="text-2xl font-bold mb-4 text-center">Proces podpisywania karty konsultacji</h3>
                <p class="text-gray-600 mb-6 text-center">
                    Podpis cyfrowy zapewnia bezpieczeństwo i integralność dokumentu. Każdy etap jest monitorowany, a dane są szyfrowane i oznaczane unikalnym SHA1.
                </p>

                {{-- Certyfikat użytkownika --}}
                <div class="flex items-center gap-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg mb-6 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-500" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 11c0-1.105.895-2 2-2s2 .895 2 2-.895 2-2 2-2-.895-2-2zm0 0v6m6-6v6m-12-6v6"/>
                    </svg>
                    <div>
                        <span class="text-gray-700 font-medium">Certyfikat użytkownika:</span>
                        <span class="font-semibold text-gray-800">{{ Auth::user()->name }}</span>
                    </div>
                </div>

                {{-- Pasek postępu --}}
                <div class="w-full bg-gray-200 rounded-full h-4 mb-6 overflow-hidden shadow-inner" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div id="progressBar" class="bg-blue-500 h-4 w-0 transition-all duration-500"></div>
                </div>

                {{-- Etapy podpisu --}}
                <ol id="signSteps" class="space-y-4">
                    @php
                        $steps = [
                            ['title' => 'Weryfikacja uprawnień systemu', 'desc' => 'Sprawdzenie uprawnień i serwera podpisu.'],
                            ['title' => 'Pobranie certyfikatu użytkownika', 'desc' => 'Certyfikat pobrany dla ' . Auth::user()->name . '.'],
                            ['title' => 'Weryfikacja integralności dokumentu XML', 'desc' => 'Sprawdzanie poprawności struktury i danych.'],
                            ['title' => 'Proces zatwierdzania dokumentu', 'desc' => 'Dokument zostaje podpisany za pomocą algorytmów.'],
                            ['title' => 'Szyfrowanie danych i generowanie SHA1', 'desc' => 'SHA1 zostaje wygenerowane i zapisane dla dokumentu.'],
                            ['title' => 'Czyszczenie danych tymczasowych i finalizacja', 'desc' => 'Pliki tymczasowe są usuwane, proces kończy się sukcesem.'],
                        ];
                    @endphp
                    @foreach($steps as $index => $step)
                        <li class="step flex items-center gap-4 p-4 border rounded-lg bg-white shadow transition duration-300 hover:scale-105">
                            <div class="step-number w-12 h-12 flex items-center justify-center rounded-full border-2 border-gray-300 text-gray-500 font-bold text-lg">{{ $index+1 }}</div>
                            <div class="flex flex-col">
                                <span class="text-gray-700 font-medium">{{ $step['title'] }}</span>
                                <span class="text-gray-400 text-sm">{{ $step['desc'] }}</span>
                            </div>
                        </li>
                    @endforeach
                </ol>

                {{-- SHA1 końcowy --}}
                <div class="mt-8 p-4 bg-gray-100 rounded-lg shadow text-center font-mono text-gray-800 text-lg">
                    SHA1 dokumentu: <span id="shaDisplay">GENEROWANY...</span>
                </div>
            </div>
        </div>

        {{-- Modal historii --}}
        <div id="historyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-6 rounded-lg max-w-lg w-full shadow-lg">
                <h3 class="text-lg font-semibold mb-4">Historia podpisów karty</h3>
                <ul id="historyList" class="list-disc list-inside space-y-1 text-gray-700 max-h-64 overflow-y-auto" tabindex="0"></ul>
                <button id="closeHistoryModal" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Zamknij
                </button>
            </div>
        </div>

    </div>

    <style>
        .step-number.completed {
            background-color: #3B82F6;
            color: white;
            border-color: #3B82F6;
        }
        .step.completed {
            background-color: #E0F2FE;
        }
    </style>

    <script>
        const steps = document.querySelectorAll('.step');
        const progressBar = document.getElementById('progressBar');

        document.querySelectorAll('.sign-button').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('signModal').classList.remove('hidden');
                let currentStep = 0;
                function highlightNextStep() {
                    if(currentStep < steps.length){
                        steps[currentStep].classList.add('completed');
                        steps[currentStep].querySelector('.step-number').classList.add('completed');
                        progressBar.style.width = ((currentStep+1)/steps.length*100) + '%';
                        currentStep++;
                        setTimeout(highlightNextStep, 700);
                    }
                }
                highlightNextStep();
            });
        });

        document.getElementById('closeSignModal').addEventListener('click', () => {
            document.getElementById('signModal').classList.add('hidden');
        });

        document.querySelectorAll('.history-button').forEach(button => {
            button.addEventListener('click', function() {
                const consultationId = this.dataset.id;
                const modal = document.getElementById('historyModal');
                const list = document.getElementById('historyList');
                list.innerHTML = '';
                modal.classList.remove('hidden');

                fetch(`/consultations/${consultationId}/history-json`)
                    .then(res => res.json())
                    .then(data => {
                        if(data.logs.length === 0){
                            list.innerHTML = '<li>Brak historii</li>';
                        } else {
                            data.logs.forEach(log => {
                                list.innerHTML += `<li>${log.created_at}: ${log.description}</li>`;
                            });
                        }
                    })
                    .catch(err => {
                        list.innerHTML = `<li>Błąd ładowania historii: ${err}</li>`;
                    });

                document.getElementById('closeHistoryModal').onclick = ()=> modal.classList.add('hidden');
            });
        });
    </script>
@endsection
