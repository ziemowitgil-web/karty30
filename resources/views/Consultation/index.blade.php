@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">

        <h1 class="text-3xl font-bold mb-6">Konsultacje</h1>

        @if(app()->environment('staging'))
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                Uwaga: Certyfikat testowy systemu jest aktywny (STAGING)
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
    </div>

    <!-- Modal podpisu -->
    <div id="signModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50" role="dialog" aria-modal="true" aria-labelledby="signModalTitle">
        <div class="bg-white p-6 rounded-xl max-w-2xl w-full shadow-lg relative">
            <button id="closeSignModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 focus:outline-none" aria-label="Zamknij modal podpisu">&times;</button>
            <h3 id="signModalTitle" class="text-2xl font-bold mb-4 text-center">Proces podpisywania karty konsultacji</h3>
            <p class="text-gray-600 mb-6 text-center">
                Podpis cyfrowy zapewnia bezpieczeństwo i integralność dokumentu. Każdy etap jest monitorowany, a dane są szyfrowane i oznaczane unikalnym SHA1.
            </p>
            <div class="flex items-center gap-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg mb-6 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-1.105.895-2 2-2s2 .895 2 2-.895 2-2 2-2-.895-2-2zm0 0v6m6-6v6m-12-6v6"/>
                </svg>
                <div>
                    <span class="text-gray-700 font-medium">Certyfikat użytkownika:</span>
                    <span class="font-semibold text-gray-800">{{ Auth::user()->name }}</span>
                </div>
            </div>

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
                    <li class="step flex items-center gap-4 p-4 border rounded-lg bg-white shadow transition duration-300 hover:scale-105">
                        <div class="step-number w-12 h-12 flex items-center justify-center rounded-full border-2 border-gray-300 text-gray-500 font-bold text-lg">{{ $index+1 }}</div>
                        <div class="flex flex-col">
                            <span class="text-gray-700 font-medium">{{ $step }}</span>
                            <span class="text-gray-400 text-sm" id="stepDesc{{ $index }}">Oczekiwanie...</span>
                        </div>
                    </li>
                @endforeach
            </ol>

            <div class="mt-8 p-4 bg-gray-100 rounded-lg shadow text-center font-mono text-gray-800 text-lg">
                SHA1 dokumentu: <span id="shaDisplay">GENEROWANY...</span>
            </div>
        </div>
    </div>

    <!-- Modal historii -->
    <div id="historyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50" role="dialog" aria-modal="true" aria-labelledby="historyModalTitle">
        <div class="bg-white p-6 rounded-lg max-w-lg w-full">
            <h3 id="historyModalTitle" class="text-lg font-semibold mb-4">Historia podpisów karty</h3>
            <ul id="historyList" class="list-disc list-inside space-y-1 text-gray-700 max-h-64 overflow-y-auto"></ul>
            <button id="closeHistoryModal" class="mt-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400">
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

            // ---------------- Podpis ----------------
            document.querySelectorAll('.sign-button').forEach(btn => {
                btn.addEventListener('click', () => {
                    const consultationId = btn.dataset.id;
                    const modal = document.getElementById('signModal');
                    modal.classList.remove('hidden');

                    const steps = modal.querySelectorAll('.step');
                    const stepDesc = Array.from(modal.querySelectorAll('[id^=stepDesc]'));
                    const progressBar = document.getElementById('progressBar');
                    let current = 0;

                    function nextStep() {
                        if(current < steps.length){
                            steps[current].classList.add('completed');
                            steps[current].querySelector('.step-number').classList.add('completed');
                            stepDesc[current].textContent = 'Wykonywanie...';
                            progressBar.style.width = ((current+1)/steps.length*100)+'%';
                            current++;
                            setTimeout(nextStep, 700);
                        } else {
                            fetch(`/consultations/${consultationId}/sign-json`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            }).then(res=>res.json())
                                .then(data=>{
                                    if(data.success){
                                        document.getElementById('shaDisplay').textContent = data.sha1;
                                        alert(data.message);
                                        location.reload();
                                    } else {
                                        alert('Błąd: '+data.error);
                                    }
                                })
                                .catch(err=>{
                                    alert('Błąd komunikacji: '+err);
                                });
                        }
                    }
                    nextStep();
                });
            });

            document.getElementById('closeSignModal').addEventListener('click', ()=> {
                document.getElementById('signModal').classList.add('hidden');
            });

            // ---------------- Historia ----------------
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
                });
            });

            document.getElementById('closeHistoryModal').addEventListener('click', ()=> {
                document.getElementById('historyModal').classList.add('hidden');
            });

        });
    </script>
@endsection
