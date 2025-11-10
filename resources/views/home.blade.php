@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-6 py-8 space-y-6" role="main" aria-label="Dashboard użytkownika">

        {{-- ALERT REDIS --}}
        @if($redisStatus !== 'Dostępny')
            <div id="redis-alert" class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg flex justify-between items-center" role="alert" aria-live="assertive">
                <span>{{ $redisStatus }} — system kolejkowania może działać wolniej.</span>
                <button onclick="this.parentElement.style.display='none'" class="ml-4 text-red-600 hover:text-red-800 font-bold text-lg" aria-label="Zamknij alert">&times;</button>
            </div>
        @endif

        {{-- ALERTY SESSION --}}
        @foreach (['warning','error','success'] as $msg)
            @if(session($msg))
                @php $colors = ['warning'=>'yellow','error'=>'red','success'=>'green']; @endphp
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
                    <p class="text-xs text-gray-500 mt-1">Podsumowanie działań i statystyki.</p>
                </header>

                {{-- STATYSTYKI --}}
                <section class="space-y-2" aria-label="Statystyki">
                    <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Statystyki</h3>

                    <div class="p-2 bg-yellow-50 border border-yellow-100 rounded-lg flex justify-between items-center">
                        <div><p class="text-gray-600 text-xs">Wersje robocze</p><p class="text-lg font-bold text-yellow-700">{{ $stats['draft'] ?? 0 }}</p></div>
                        <i class="fas fa-pencil-alt text-yellow-700" aria-hidden="true"></i>
                    </div>

                    <div class="p-2 bg-green-50 border border-green-100 rounded-lg flex justify-between items-center">
                        <div><p class="text-gray-600 text-xs">Zatwierdzone</p><p class="text-lg font-bold text-green-700">{{ $stats['completed'] ?? 0 }}</p></div>
                        <i class="fas fa-check-circle text-green-700" aria-hidden="true"></i>
                    </div>

                    <div class="p-2 bg-red-50 border border-red-100 rounded-lg flex justify-between items-center">
                        <div><p class="text-gray-600 text-xs">Anulowane</p><p class="text-lg font-bold text-red-700">{{ $stats['cancelled'] ?? 0 }}</p></div>
                        <i class="fas fa-times-circle text-red-700" aria-hidden="true"></i>
                    </div>
                </section>

                {{-- OSTATNIE AKCJE --}}
                <section class="mt-4" aria-label="Ostatnie akcje">
                    <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Ostatnie akcje</h3>
                    @if($recentActions->isEmpty())
                        <p class="text-gray-500 text-xs">Brak wykonanych akcji.</p>
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
                    <x-action-tile href="{{ route('consultations.create') }}" color="blue" icon="stethoscope" label="Nowa konsultacja"/>
                    <x-action-tile href="{{ route('schedules.create') }}" color="indigo" icon="calendar-plus" label="Nowa rezerwacja"/>
                    <x-action-tile href="{{ route('clients.index') }}" color="green" icon="users" label="Lista klientów"/>
                    <x-action-tile href="{{ route('raport') }}" color="gray" icon="file-alt" label="Raporty"/>
                </section>

                {{-- CERTYFIKAT UŻYTKOWNIKA --}}
                @php
                    $certFilePath = storage_path('app/certificates/' . auth()->user()->id . '_user_cert.pem');
                    $certExists = file_exists($certFilePath);
                    $certInfo = $certExists ? shell_exec("openssl x509 -in {$certFilePath} -noout -subject -dates") : null;

                    $certStatus = 'Brak certyfikatu';
                    $certCN = '-';
                    $certValidFrom = '-';
                    $certValidTo = '-';

                    if($certInfo) {
                        preg_match('/subject= (.*)/', $certInfo, $m); $certCN = $m[1] ?? '-';
                        preg_match('/notBefore=(.*)/', $certInfo, $m); $certValidFrom = $m[1] ?? '-';
                        preg_match('/notAfter=(.*)/', $certInfo, $m); $certValidTo = $m[1] ?? '-';
                        $now = new \DateTime();
                        $notAfter = new \DateTime($certValidTo);
                        $certStatus = $now < $notAfter ? 'Ważny' : 'Wygasły';
                    }

                    $statusColors = ['Ważny'=>'green','Wygasły'=>'red','Brak certyfikatu'=>'gray'];
                @endphp

                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 space-y-3" aria-label="Informacje o certyfikacie użytkownika">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Certyfikat użytkownika</h2>
                    @if(!$certExists)
                        <p class="text-gray-500 text-sm">Nie znaleziono certyfikatu w systemie.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="p-3 bg-{{ $statusColors[$certStatus] }}-100 rounded-lg flex flex-col">
                                <span class="text-xs text-gray-600">Status</span>
                                <span class="font-semibold text-{{ $statusColors[$certStatus] }}-700">{{ $certStatus }}</span>
                            </div>
                            <div class="p-3 bg-blue-50 rounded-lg flex flex-col">
                                <span class="text-xs text-gray-600">Posiadacz (CN)</span>
                                <span class="font-semibold text-blue-700 truncate" title="{{ $certCN }}">{{ $certCN }}</span>
                            </div>
                            <div class="p-3 bg-purple-50 rounded-lg flex flex-col">
                                <span class="text-xs text-gray-600">Ważność</span>
                                <span class="font-semibold text-purple-700">{{ $certValidFrom }} → {{ $certValidTo }}</span>
                            </div>
                        </div>
                    @endif
                    <p class="text-gray-500 text-xs mt-2">Kliknij przycisk, aby zobaczyć pełne szczegóły certyfikatu.</p>
                    <a href="{{ route('consultations.certificate.view') }}" class="w-full inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white py-1.5 rounded-md text-sm transition-colors">
                        Pokaż szczegóły certyfikatu
                    </a>
                </section>

                {{-- POŚWIADCZENIA --}}
                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 space-y-2">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Poświadczenia</h2>
                    @if(config('app.test_mode') != 1)
                        <p class="text-gray-500 text-xs">Użyj <strong>klucza bezpieczeństwa (WebAuthn)</strong> lub <strong>certyfikatu PFX</strong> do logowania i podpisywania dokumentów.</p>
                        <button id="register-key-btn" class="w-full mb-1 bg-gray-800 hover:bg-gray-900 text-white py-1.5 rounded-md text-sm transition-colors">Zarejestruj klucz</button>
                        <a href="#" class="w-full inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white py-1.5 rounded-md text-sm transition-colors">Użyj certyfikatu PFX</a>
                    @else
                        <p class="text-yellow-800 text-sm">Tryb testowy: podpisane karty nie mają mocy prawnej, obsługa PFX wyłączona.</p>
                    @endif
                </section>

                {{-- REZERWACJE DZISIAJ --}}
                <x-schedule-table :schedules="$todaySchedules" title="Zaplanowane na dziś"/>
                {{-- REZERWACJE 7 DNI --}}
                <x-schedule-table :schedules="$weekSchedules" title="Następne 7 dni"/>

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
