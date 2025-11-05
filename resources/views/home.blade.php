@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-6 py-8 space-y-6" role="main" aria-label="Dashboard użytkownika">

        {{-- ALERT REDIS --}}
        @if($redisStatus !== 'Dostępny')
            <div id="redis-alert" class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg flex justify-between items-center" role="alert" aria-live="assertive">
                <span>{{ $redisStatus }} — prosimy o cierpliwość, system kolejkowania może działać wolniej.</span>
                <button onclick="this.parentElement.style.display='none'" class="ml-4 text-red-600 hover:text-red-800 font-bold text-lg" aria-label="Zamknij alert">&times;</button>
            </div>
        @endif

        {{-- ALERTY SESSION --}}
        @foreach (['warning','error','success'] as $msg)
            @if(session($msg))
                @php
                    $colors = ['warning'=>'yellow','error'=>'red','success'=>'green'];
                @endphp
                <div class="mb-4 p-4 bg-{{ $colors[$msg] }}-100 border border-{{ $colors[$msg] }}-300 text-{{ $colors[$msg] }}-800 rounded-lg" role="status" aria-live="polite">
                    {{ session($msg) }}
                </div>
            @endif
        @endforeach

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            {{-- PANEL BOCZNY --}}
            <aside class="lg:col-span-1 bg-white border border-gray-200 rounded-2xl shadow-sm p-4 space-y-6" role="complementary" aria-label="Panel boczny">
                <header class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Witaj, {{ auth()->user()->name }}</h2>
                    <p class="text-xs text-gray-500 mt-1">Tutaj znajdziesz podsumowanie swoich działań i statystyki.</p>
                </header>

                {{-- STATYSTYKI --}}
                <section class="space-y-2" aria-label="Statystyki konsultacji">
                    <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Statystyki</h3>

                    <div class="p-2 bg-yellow-50 border border-yellow-100 rounded-lg flex justify-between items-center" aria-label="Liczba wersji roboczych">
                        <div>
                            <p class="text-gray-600 text-xs">Wersje robocze</p>
                            <p class="text-lg font-bold text-yellow-700">{{ $stats['draft'] ?? 0 }}</p>
                        </div>
                        <i class="fas fa-pencil-alt text-yellow-700" aria-hidden="true"></i>
                    </div>

                    <div class="p-2 bg-green-50 border border-green-100 rounded-lg flex justify-between items-center" aria-label="Liczba zatwierdzonych konsultacji">
                        <div>
                            <p class="text-gray-600 text-xs">Zatwierdzone</p>
                            <p class="text-lg font-bold text-green-700">{{ $stats['completed'] ?? 0 }}</p>
                        </div>
                        <i class="fas fa-check-circle text-green-700" aria-hidden="true"></i>
                    </div>

                    <div class="p-2 bg-red-50 border border-red-100 rounded-lg flex justify-between items-center" aria-label="Liczba anulowanych konsultacji">
                        <div>
                            <p class="text-gray-600 text-xs">Anulowane</p>
                            <p class="text-lg font-bold text-red-700">{{ $stats['cancelled'] ?? 0 }}</p>
                        </div>
                        <i class="fas fa-times-circle text-red-700" aria-hidden="true"></i>
                    </div>
                </section>

                {{-- OSTATNIE AKCJE --}}
                <section class="mt-4" aria-label="Ostatnie akcje użytkownika">
                    <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Ostatnie akcje</h3>
                    @if($recentActions->isEmpty())
                        <p class="text-gray-500 text-xs">Nie wykonano jeszcze żadnych akcji.</p>
                    @else
                        <ul class="divide-y divide-gray-200 max-h-60 overflow-y-auto text-xs">
                            @foreach($recentActions as $action)
                                <li class="py-1 flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $action->action_type }}</p>
                                        <p class="text-gray-500">{{ $action->created_at->format('d.m H:i') }} — {{ $action->target_name ?? '-' }}</p>
                                    </div>
                                    <span class="inline-block px-1 py-0.5 rounded text-gray-600 bg-gray-100">{{ $action->status_label ?? '-' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </aside>

            {{-- PANEL GŁÓWNY --}}
            <main class="lg:col-span-3 space-y-6" role="region" aria-label="Panel główny">

                {{-- KAFELKI AKCJI --}}
                <section class="grid grid-cols-2 sm:grid-cols-4 gap-3" aria-label="Skróty akcji">
                    <a href="{{ route('consultations.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl p-3 flex flex-col items-center justify-center shadow-sm transition-transform hover:-translate-y-0.5" aria-label="Dodaj nową konsultację">
                        <i class="fas fa-stethoscope text-base mb-1"></i>
                        <span class="font-medium text-sm">Nowa konsultacja</span>
                    </a>
                    <a href="{{ route('schedules.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl p-3 flex flex-col items-center justify-center shadow-sm transition-transform hover:-translate-y-0.5" aria-label="Dodaj nową rezerwację">
                        <i class="fas fa-calendar-plus text-base mb-1"></i>
                        <span class="font-medium text-sm">Nowa rezerwacja</span>
                    </a>
                    <a href="{{ route('clients.index') }}" class="bg-green-600 hover:bg-green-700 text-white rounded-xl p-3 flex flex-col items-center justify-center shadow-sm transition-transform hover:-translate-y-0.5" aria-label="Lista klientów">
                        <i class="fas fa-users text-base mb-1"></i>
                        <span class="font-medium text-sm">Lista klientów</span>
                    </a>
                    <a href="{{ route('raport') }}" class="bg-gray-700 hover:bg-gray-800 text-white rounded-xl p-3 flex flex-col items-center justify-center shadow-sm transition-transform hover:-translate-y-0.5" aria-label="Przegląd raportów">
                        <i class="fas fa-file-alt text-base mb-1"></i>
                        <span class="font-medium text-sm">Raporty</span>
                    </a>
                </section>

                {{-- POŚWIADCZENIA / TRYB TEST --}}
                @if(config('app.test_mode') != 1)
                    <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 space-y-2" aria-label="Poświadczenia użytkownika">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">Poświadczenia</h2>
                        <p class="text-gray-500 text-xs">Możesz użyć <strong>klucza bezpieczeństwa (WebAuthn)</strong> lub <strong>certyfikatu PFX</strong> do logowania i podpisywania dokumentów.</p>
                        <button id="register-key-btn" class="w-full mb-1 bg-gray-800 hover:bg-gray-900 text-white py-1.5 rounded-md text-sm transition-colors" aria-label="Zarejestruj klucz bezpieczeństwa">Zarejestruj klucz</button>
                        <a href="#" class="w-full inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white py-1.5 rounded-md text-sm transition-colors" aria-label="Użyj certyfikatu PFX">Użyj certyfikatu PFX</a>
                    </section>
                @else
                    <section class="bg-yellow-50 border border-yellow-200 rounded-2xl shadow-sm p-4">
                        <p class="text-yellow-800 text-sm">Tryb testowy: podpisane i zatwierdzone karty nie mają mocy prawnej, obsługa PFX jest wyłączona.</p>
                    </section>
                @endif

                {{-- DZISIEJSZE REZERWACJE --}}
                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4" aria-label="Rezerwacje na dziś">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Zaplanowane na dziś</h2>
                    @if($todaySchedules->isEmpty())
                        <p class="text-gray-500 text-xs">Brak zaplanowanych rezerwacji na dzisiaj.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm divide-y divide-gray-200" role="table">
                                <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th scope="col" class="text-left px-2 py-1 font-semibold">Godzina</th>
                                    <th scope="col" class="text-left px-2 py-1 font-semibold">Klient</th>
                                    <th scope="col" class="text-left px-2 py-1 font-semibold">Status</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($todaySchedules as $schedule)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-2 py-1">{{ $schedule->start_time->format('H:i') }}</td>
                                        <td class="px-2 py-1">{{ $schedule->client->name ?? '-' }}</td>
                                        <td class="px-2 py-1">{{ $schedule->status_label }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                {{-- NAJBLIŻSZE 7 DNI --}}
                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4" aria-label="Rezerwacje na najbliższe 7 dni">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Następne 7 dni</h2>
                    @if($weekSchedules->isEmpty())
                        <p class="text-gray-500 text-xs">Brak zaplanowanych rezerwacji w ciągu najbliższego tygodnia.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm divide-y divide-gray-200" role="table">
                                <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th scope="col" class="text-left px-2 py-1 font-semibold">Data</th>
                                    <th scope="col" class="text-left px-2 py-1 font-semibold">Godzina</th>
                                    <th scope="col" class="text-left px-2 py-1 font-semibold">Klient</th>
                                    <th scope="col" class="text-left px-2 py-1 font-semibold">Status</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($weekSchedules as $schedule)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-2 py-1">{{ $schedule->start_time->format('d.m.Y') }}</td>
                                        <td class="px-2 py-1">{{ $schedule->start_time->format('H:i') }}</td>
                                        <td class="px-2 py-1">{{ $schedule->client->name ?? '-' }}</td>
                                        <td class="px-2 py-1">{{ $schedule->status_label }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

            </main>
        </div>
    </div>

    {{-- SKRYPTY WEBAUTHN --}}
    <script>
        function bufferToBase64Url(buffer) {
            const bytes = new Uint8Array(buffer);
            let str = '';
            for (let i = 0; i < bytes.byteLength; i++) str += String.fromCharCode(bytes[i]);
            return btoa(str).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        }

        @if(config('app.test_mode') != 1)
        document.getElementById('register-key-btn').addEventListener('click', async () => {
            try {
                const optionsResponse = await fetch('/webauthn/keys/options', { headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} });
                const options = await optionsResponse.json();

                options.challenge = Uint8Array.from(atob(options.challenge.replace(/-/g,'+').replace(/_/g,'/')), c => c.charCodeAt(0));
                options.user.id = Uint8Array.from(atob(options.user.id.replace(/-/g,'+').replace(/_/g,'/')), c => c.charCodeAt(0));

                const credential = await navigator.credentials.create({ publicKey: options });

                const body = {
                    id: credential.id,
                    rawId: bufferToBase64Url(credential.rawId),
                    type: credential.type,
                    response: {
                        attestationObject: bufferToBase64Url(credential.response.attestationObject),
                        clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON)
                    }
                };

                const res = await fetch('/webauthn/keys/register', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                    body: JSON.stringify(body)
                });

                if(res.ok) alert('Klucz został zarejestrowany!');
                else alert('Błąd podczas rejestracji klucza.');
            } catch(err) {
                console.error(err);
                alert('Błąd podczas rejestracji klucza.');
            }
        });
        @endif
    </script>
@endsection
