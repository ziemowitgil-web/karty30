@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Konsultacje</h1>

        {{-- Środowisko staging --}}
        @if(app()->environment('staging'))
            <div class="border-2 border-yellow-400 bg-yellow-50 p-4 rounded-lg mb-6 flex items-start gap-4">
                <img src="https://cdn-icons-png.flaticon.com/512/1828/1828843.png" alt="Ostrzeżenie" class="w-10 h-10">
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
                                    data-id="{{ $c->id }}">
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

        <!-- Modal podpisu -->
        <div id="signModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-6 rounded-xl max-w-2xl w-full shadow-lg relative">
                <h3 class="text-2xl font-bold mb-4 text-center">Proces podpisywania karty konsultacji</h3>

                <div class="w-full bg-gray-200 rounded-full h-4 mb-6 overflow-hidden shadow-inner">
                    <div id="progressBar" class="bg-blue-500 h-4 w-0 transition-all duration-500"></div>
                </div>

                <ol id="signSteps" class="space-y-4">
                    @foreach([
                        'Weryfikacja uprawnień systemu',
                        'Pobranie certyfikatu użytkownika',
                        'Weryfikacja integralności dokumentu XML',
                        'Proces zatwierdzania dokumentu',
                        'Szyfrowanie danych i generowanie SHA1',
                        'Czyszczenie danych tymczasowych i finalizacja'
                    ] as $index => $step)
                        <li class="step flex items-center gap-4 p-4 border rounded-lg bg-white shadow transition duration-300">
                            <div class="step-number w-10 h-10 flex items-center justify-center rounded-full border-2 border-gray-300 text-gray-500 font-bold">{{ $index+1 }}</div>
                            <div>
                                <span class="text-gray-700 font-medium">{{ $step }}</span>
                                <span class="text-gray-400 text-sm block" id="stepDesc{{ $index }}">Oczekiwanie...</span>
                            </div>
                        </li>
                    @endforeach
                </ol>

                <div class="mt-8 p-4 bg-gray-100 rounded-lg shadow text-center font-mono text-gray-800 text-lg">
                    SHA1 dokumentu: <span id="shaDisplay">GENEROWANY...</span>
                </div>

                <div id="autoReturnMessage" class="mt-4 text-center text-gray-700 font-medium hidden">
                    <span id="finalMessage"></span><br>
                    Powrót do strony głównej za <span id="returnCountdown">10</span> sekund...
                </div>

                <button id="closeSignModal" class="mt-6 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 w-full">
                    Zamknij
                </button>
            </div>
        </div>

        <!-- Modal historii -->
        <div id="historyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-6 rounded-lg max-w-lg w-full">
                <h3 class="text-lg font-semibold mb-4">Historia podpisów karty</h3>
                <ul id="historyList" class="list-disc list-inside space-y-1 text-gray-700 max-h-64 overflow-y-auto"></ul>
                <button id="closeHistoryModal" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Zamknij
                </button>
            </div>
        </div>

        <style>
            .step-number.completed { background-color: #3B82F6; color: white; border-color: #3B82F6; }
            .step.completed { background-color: #E0F2FE; }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.sign-button').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const consultationId = btn.dataset.id;
                        const modal = document.getElementById('signModal');
                        modal.classList.remove('hidden');

                        const steps = modal.querySelectorAll('.step');
                        const stepDesc = Array.from(modal.querySelectorAll('[id^=stepDesc]'));
                        const progressBar = document.getElementById('progressBar');
                        const autoReturnMessage = document.getElementById('autoReturnMessage');
                        const finalMessage = document.getElementById('finalMessage');
                        const returnCountdown = document.getElementById('returnCountdown');
                        let current = 0;

                        function nextStep() {
                            if (current < steps.length) {
                                steps[current].classList.add('completed');
                                steps[current].querySelector('.step-number').classList.add('completed');
                                stepDesc[current].textContent = 'Wykonywanie...';
                                progressBar.style.width = ((current + 1) / steps.length * 100) + '%';
                                current++;
                                setTimeout(nextStep, 700);
                            } else {
                                fetch(`/consultations/${consultationId}/sign-json`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                }).then(res => res.json())
                                    .then(data => {
                                        autoReturnMessage.classList.remove('hidden');
                                        let countdown = 10;
                                        const interval = setInterval(() => {
                                            countdown--;
                                            returnCountdown.textContent = countdown;
                                            if (countdown <= 0) {
                                                clearInterval(interval);
                                                window.location.href = '/home';
                                            }
                                        }, 1000);

                                        if (data.success) {
                                            document.getElementById('shaDisplay').textContent = data.sha1;
                                            finalMessage.textContent = ' Podpis zakończony sukcesem. Dziękujemy!';
                                        } else {
                                            finalMessage.textContent = ' Wystąpił błąd podczas podpisu: ' + (data.error || 'Nieznany błąd.');
                                        }
                                    })
                                    .catch(err => {
                                        autoReturnMessage.classList.remove('hidden');
                                        finalMessage.textContent = '️ Błąd komunikacji z serwerem: ' + err.message;
                                        let countdown = 10;
                                        const interval = setInterval(() => {
                                            countdown--;
                                            returnCountdown.textContent = countdown;
                                            if (countdown <= 0) {
                                                clearInterval(interval);
                                                window.location.href = '/home';
                                            }
                                        }, 1000);
                                    });
                            }
                        }
                        nextStep();
                    });
                });

                document.getElementById('closeSignModal').addEven
