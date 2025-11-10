@extends('layouts.app')

@section('content')
    @php
        $certDir = storage_path('app/certificates');
        $certFile = $certDir . '/' . Auth::user()->id . '_user_cert.pem';
        $certExists = file_exists($certFile);

        $certCN = 'Brak certyfikatu';
        $certValidUntil = null;

        if ($certExists) {
            $certContent = file_get_contents($certFile);
            $certInfo = openssl_x509_parse($certContent);
            $certCN = $certInfo['subject']['CN'] ?? 'Nieznany';
            $certValidUntil = isset($certInfo['validTo_time_t']) ? date('d.m.Y', $certInfo['validTo_time_t']) : null;
        }
    @endphp

    <div class="container mx-auto px-6 py-8 space-y-6" role="main" aria-label="Dashboard użytkownika">

        {{-- ALERT REDIS --}}
        @if($redisStatus !== 'Dostępny')
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg flex justify-between items-center" role="alert" aria-live="assertive">
                <span>{{ $redisStatus }} — prosimy o cierpliwość, system może działać wolniej.</span>
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

        {{-- OSTRZEŻENIE O BRAKU CERTYFIKATU --}}
        @unless($certExists)
            <div class="p-4 bg-red-100 border border-red-400 text-red-800 rounded-xl shadow-sm animate-pulse" role="alert" aria-live="assertive">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-exclamation-triangle text-xl text-red-600"></i>
                        <div>
                            <p class="font-semibold text-red-800">Nie posiadasz ważnego certyfikatu!</p>
                            <p class="text-sm text-red-700">Nie możesz podpisywać żadnych dokumentów, dopóki nie wygenerujesz nowego certyfikatu.</p>
                        </div>
                    </div>
                    <a href="{{ route('consultations.certificate.view') }}" class="ml-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                        Wygeneruj teraz
                    </a>
                </div>
            </div>
        @endunless

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
                        <div>
                            <p class="text-gray-600 text-xs">Wersje robocze</p>
                            <p class="text-lg font-bold text-yellow-700">{{ $stats['draft'] ?? 0 }}</p>
                        </div>
                        <i class="fas fa-pencil-alt text-yellow-700" aria-hidden="true"></i>
                    </div>

                    <div class="p-2 bg-green-50 border border-green-100 rounded-lg flex justify-between items-center">
                        <div>
                            <p class="text-gray-600 text-xs">Zatwierdzone</p>
                            <p class="text-lg font-bold text-green-700">{{ $stats['completed'] ?? 0 }}</p>
                        </div>
                        <i class="fas fa-check-circle text-green-700" aria-hidden="true"></i>
                    </div>

                    <div class="p-2 bg-red-50 border border-red-100 rounded-lg flex justify-between items-center">
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
                <section class="grid grid-cols-2 sm:grid-cols-5 gap-3" aria-label="Skróty akcji">
                    <a href="{{ route('consultations.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl p-3 flex flex-col items-center justify-center shadow-sm transition-transform hover:-translate-y-0.5">
                        <i class="fas fa-stethoscope text-base mb-1"></i>
                        <span class="font-medium text-sm">Nowa konsultacja</span>
                    </a>
                    <a href="{{ route('schedules.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl p-3 flex flex-col items-center justify-center shadow-sm transition-transform hover:-translate-y-0.5">
                        <i class="fas fa-calendar-plus text-base mb-1"></i>
                        <span class="font-medium text-sm">Nowa rezerwacja</span>
                    </a>
                    <a href="{{ route('clients.index') }}" class="bg-green-600 hover:bg-green-700 text-white rounded-xl p-3 flex flex-col items-center justify-center shadow-sm transition-transform hover:-translate-y-0.5">
                        <i class="fas fa-users text-base mb-1"></i>
                        <span class="font-medium text-sm">Lista klientów</span>
                    </a>
                    <a href="{{ route('raport') }}" class="bg-gray-700 hover:bg-gray-800 text-white rounded-xl p-3 flex flex-col items-center justify-center shadow-sm transition-transform hover:-translate-y-0.5">
                        <i class="fas fa-file-alt text-base mb-1"></i>
                        <span class="font-medium text-sm">Raporty</span>
                    </a>
                    <a href="{{ route('consultations.certificate.view') }}" class="bg-yellow-100 hover:bg-yellow-200 text-gray-800 rounded-xl p-3 flex flex-col items-center justify-center shadow-sm transition-transform hover:-translate-y-0.5">
                        <i class="fas fa-id-card text-base mb-1"></i>
                        <span class="font-medium text-sm">Zarządzaj certyfikatem</span>
                    </a>
                </section>

                {{-- INFORMACJE O CERTYFIKACIE --}}
                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 space-y-2">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Twój certyfikat</h2>

                    @if($certExists)
                        <p class="text-gray-700 text-sm">CN: <strong>{{ $certCN }}</strong></p>
                        @if($certValidUntil)
                            <p class="text-gray-700 text-sm">Ważny do: <strong>{{ $certValidUntil }}</strong></p>
                        @endif
                    @else
                        <p class="text-red-700 font-medium text-sm">Brak certyfikatu – nie możesz podpisywać dokumentów.</p>
                    @endif
                </section>

                {{-- DZISIEJSZE REZERWACJE --}}
                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4" aria-label="Rezerwacje na dziś">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Zaplanowane na dziś</h2>
                    @if($todaySchedules->isEmpty())
                        <p class="text-gray-500 text-xs">Brak zaplanowanych rezerwacji na dzisiaj.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm divide-y divide-gray-200">
                                <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="text-left px-2 py-1 font-semibold">Godzina</th>
                                    <th class="text-left px-2 py-1 font-semibold">Klient</th>
                                    <th class="text-left px-2 py-1 font-semibold">Status</th>
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
                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Następne 7 dni</h2>
                    @if($weekSchedules->isEmpty())
                        <p class="text-gray-500 text-xs">Brak zaplanowanych rezerwacji w ciągu najbliższego tygodnia.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm divide-y divide-gray-200">
                                <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="text-left px-2 py-1 font-semibold">Data</th>
                                    <th class="text-left px-2 py-1 font-semibold">Godzina</th>
                                    <th class="text-left px-2 py-1 font-semibold">Klient</th>
                                    <th class="text-left px-2 py-1 font-semibold">Status</th>
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
@endsection
