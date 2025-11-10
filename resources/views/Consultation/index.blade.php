@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Konsultacje</h1>

        {{-- Środowisko staging --}}
        @if(app()->environment('staging'))
            <div class="border-2 border-yellow-600 bg-yellow-50 p-4 rounded-lg mb-6 flex items-start gap-4" role="alert">
                <img src="https://cdn-icons-png.flaticon.com/512/1828/1828843.png" alt="Ostrzeżenie" class="w-10 h-10 flex-shrink-0">
                <div>
                    <p class="text-yellow-900 font-semibold mb-2">Uwaga: Certyfikat testowy systemu jest aktywny (STAGING)</p>
                    <form method="POST" action="{{ route('consultations.deleteTestData') }}">
                        @csrf
                        <button type="submit"
                                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500">
                            Usuń dane testowe
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <a href="{{ route('consultations.create') }}"
           class="bg-green-800 text-white px-4 py-2 rounded hover:bg-green-700 focus:outline-none focus:ring-3 focus:ring-offset-2 focus:ring-green-500 mb-6 inline-block">
            Nowa karta konsultacyjna
        </a>

        {{-- Niepodpisane --}}
        <h2 class="text-xl font-semibold mt-6 mb-2">Niepodpisane</h2>
        <div class="overflow-x-auto shadow rounded-lg border border-gray-300 mb-6">
            <table class="min-w-full divide-y divide-gray-300 text-sm" role="table">
                <thead class="bg-gray-100">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">ID</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">Klient</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">Data i godzina</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">Czas trwania</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">Akcje</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($consultations->where('status','draft') as $c)
                    <tr class="hover:bg-gray-50 transition">
                        <th scope="row" class="px-4 py-2 font-medium text-gray-900">{{ $c->id }}</th>
                        <td class="px-4 py-2">{{ $c->client->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($c->consultation_datetime)->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $c->duration_minutes }} min</td>
                        <td class="px-4 py-2 flex flex-wrap gap-2">
                            @php
                                $userCertPath = storage_path('app/certificates/' . auth()->id() . '_user_cert.pem');
                                $userCertExists = file_exists($userCertPath);
                                $userCertData = $userCertExists ? openssl_x509_parse(file_get_contents($userCertPath)) : null;
                            @endphp
                            <button class="sign-button bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500 disabled:opacity-50"
                                    data-id="{{ $c->id }}" @if(!$userCertExists) disabled aria-disabled="true" @endif>
                                Podpisz
                            </button>
                            <button class="history-button bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-500"
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
        <div class="overflow-x-auto shadow rounded-lg border border-gray-300">
            <table class="min-w-full divide-y divide-gray-300 text-sm" role="table">
                <thead class="bg-gray-100">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">ID</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">Klient</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">Data i godzina</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">Czas trwania</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">Odcisk palca (SHA1)</th>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-800">Akcje</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($consultations->where('status','completed') as $c)
                    <tr class="hover:bg-gray-50 transition">
                        <th scope="row" class="px-4 py-2 font-medium text-gray-900">{{ $c->id }}</th>
                        <td class="px-4 py-2">{{ $c->client->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($c->consultation_datetime)->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $c->duration_minutes }} min</td>
                        <td class="px-4 py-2 font-mono">{{ $c->sha1sum ?? '-' }}</td>
                        <td class="px-4 py-2 flex flex-wrap gap-2">
                            <button class="history-button bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-500"
                                    data-id="{{ $c->id }}">
                                Historia
                            </button>
                            <a href="{{ route('consultations.pdf', $c) }}" target="_blank"
                               class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                                Drukuj
                            </a>
                            <a href="{{ route('consultations.xml', $c) }}" target="_blank"
                               class="bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700 text-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-purple-500">
                                Podgląd XML
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modal podpisu -->
        <div id="signModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50" role="dialog" aria-modal="true" aria-labelledby="signModalTitle">
            <div class="bg-white p-6 rounded-xl max-w-2xl w-full shadow-lg relative">
                <h3 id="signModalTitle" class="text-2xl font-bold mb-4 text-center">Proces podpisywania karty konsultacji</h3>

                <div class="w-full bg-gray-200 rounded-full h-4 mb-6 overflow-hidden shadow-inner" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="progressBar"></div>

                <ol id="signSteps" class="space-y-4">
                    @foreach([
                        'Weryfikacja uprawnień systemu',
                        'Pobranie certyfikatu użytkownika',
                        'Weryfikacja integralności dokumentu XML',
                        'Proces zatwierdzania dokumentu',
                        'Szyfrowanie danych i generowanie SHA1',
                        'Finalizacja i zapis dokumentu'
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

                <div class="mt-6 border-t border-gray-300 pt-4">
                    <h4 class="text-gray-700 font-semibold mb-2">Certyfikat użytkownika</h4>
                    @if($userCertData)
                        <ul class="text-gray-700 text-sm space-y-1 mb-2">
                            <li><strong>CN:</strong> {{ $userCertData['subject']['CN'] ?? '-' }}</li>
                            <li><strong>Email:</strong> {{ $userCertData['subject']['emailAddress'] ?? '-' }}</li>
                            <li><strong>Organizacja:</strong> {{ $userCertData['subject']['O'] ?? '-' }}</li>
                            <li><strong>Jednostka (OU):</strong> {{ $userCertData['subject']['OU'] ?? '-' }}</li>
                            <li><strong>SHA1:</strong> <span id="certSha1" class="font-mono">{{ sha1(file_get_contents($userCertPath)) }}</span></li>
                            <li><strong>Ważny od:</strong> {{ isset($userCertData['validFrom_time_t']) ? date('c',$userCertData['validFrom_time_t']) : '-' }}</li>
                            <li><strong>Ważny do:</strong> {{ isset($userCertData['validTo_time_t']) ? date('c',$userCertData['validTo_time_t']) : '-' }}</li>
                        </ul>
                    @else
                        <p class="text-red-700 font-semibold">Brak aktywnego certyfikatu!</p>
                    @endif
                </div>

                <div class="mt-4 p-4 bg-gray-100 rounded-lg shadow text-center font-mono text-gray-800 text-lg">
                    SHA1 dokumentu: <span id="shaDisplay">GENEROWANY...</span>
                </div>

                <div id="autoReturnMessage" class="mt-4 text-center text-gray-700 font-medium hidden">
                    <span id="finalMessage"></span><br>
                    Powrót do strony głównej za <span id="returnCountdown">10</span> sekund...
                </div>

                <button id="closeSignModal" class="mt-6 bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 w-full focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-500">
                    Zamknij
                </button>
            </div>
        </div>

        <!-- Modal historii -->
        <div id="historyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50" role="dialog" aria-modal="true" aria-labelledby="historyModalTitle">
            <div class="bg-white p-6 rounded-lg max-w-lg w-full">
                <h3 id="historyModalTitle" class="text-lg font-semibold mb-4">Historia podpisów karty</h3>
                <ul id="historyList" class="list-disc list-inside space-y-1 text-gray-700 max-h-64 overflow-y-auto"></ul>
                <button id="closeHistoryModal" class="mt-4 bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-500">
                    Zamknij
                </button>
            </div>
        </div>

        <style>
            .step-number.completed { background-color: #2563EB; color: white; border-color: #2563EB; }
            .step.completed { background-color: #DBEAFE; }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const userCertExists = {!! $userCertExists ? 'true' : 'false' !!};

                document.querySelectorAll('.sign-button').forEach(btn => {
                    btn.addEventListener('click', () => {
                        if(!userCertExists) return;

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
                                progressBar.setAttribute('aria-valuenow', ((current + 1) / steps.length * 100).toFixed(0));
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
                                            finalMessage.textContent = 'Podpis zakończony sukcesem.';
                                        } else {
                                            finalMessage.textContent = 'Błąd podczas podpisu: ' + (data.error || 'Nieznany błąd.');
                                        }
                                    }).catch(err => {
                                    autoReturnMessage.classList.remove('hidden');
                                    finalMessage.textContent = 'Błąd komunikacji z serwerem: ' + err.message;
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
                                if (data.logs.length === 0) list.innerHTML = '<li>Brak historii</li>';
                                else data.logs.forEach(log => list.innerHTML += `<li>${log.created_at}: ${log.description}</li>`);
                            })
                            .catch(err => list.innerHTML = `<li>Błąd ładowania historii: ${err}</li>`);
                    });
                });

                document.getElementById('closeHistoryModal').addEventListener('click', () => {
                    document.getElementById('historyModal').classList.add('hidden');
                });
            });
        </script>
    </div>
@endsection
