@extends('layouts.app')

@section('content')
    @php
        $certDir = storage_path('app/certificates');
        $certFile = $certDir . '/' . Auth::user()->id . '_user_cert.pem';
        $certExists = file_exists($certFile);

        $certCN = 'Brak certyfikatu';
        $certOrg = null;
        $certValidUntil = null;
        $certExpiringSoon = false;

        if ($certExists) {
            $certContent = file_get_contents($certFile);
            $certInfo = openssl_x509_parse($certContent);
            $certCN = $certInfo['subject']['CN'] ?? 'Nieznany użytkownik';
            $certOrg = $certInfo['subject']['O'] ?? 'Brak danych o organizacji';
            if (isset($certInfo['validTo_time_t'])) {
                $certValidUntil = date('d.m.Y', $certInfo['validTo_time_t']);
                $daysLeft = ($certInfo['validTo_time_t'] - time()) / 86400;
                $certExpiringSoon = $daysLeft <= 10;
            }
        }
    @endphp

    <div class="container mx-auto px-6 py-8 space-y-6" role="main" aria-label="Panel główny użytkownika">

        {{-- KOMUNIKAT REDIS --}}
        @if($redisStatus !== 'Dostępny')
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex justify-between items-center" role="alert">
                <span><strong>Uwaga:</strong> {{ $redisStatus }} — system może działać wolniej.</span>
                <button onclick="this.parentElement.remove()" aria-label="Zamknij" class="ml-3 text-red-600 font-bold text-lg">&times;</button>
            </div>
        @endif

        {{-- ALERTY SESJI --}}
        @foreach (['warning','error','success'] as $msg)
            @if(session($msg))
                @php $colors = ['warning'=>'yellow','error'=>'red','success'=>'green']; @endphp
                <div class="p-4 bg-{{ $colors[$msg] }}-50 border border-{{ $colors[$msg] }}-200 text-{{ $colors[$msg] }}-800 rounded-xl">
                    {{ session($msg) }}
                </div>
            @endif
        @endforeach

        {{-- CERTYFIKAT --}}
        <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 space-y-3" aria-label="Informacje o certyfikacie">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                <i class="fas fa-shield-alt text-blue-600"></i> Twój certyfikat
            </h2>

            @if($certExists)
                <div class="text-gray-700 leading-relaxed space-y-1">
                    <p><span class="font-medium text-gray-900">CN:</span> {{ $certCN }}</p>
                    <p><span class="font-medium text-gray-900">Organizacja:</span> {{ $certOrg }}</p>
                    <p><span class="font-medium text-gray-900">Ważny do:</span> {{ $certValidUntil }}</p>

                    @if($certExpiringSoon)
                        <div class="p-3 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Twój certyfikat wkrótce wygaśnie. Skontaktuj się z administratorem w celu odnowienia.
                        </div>
                    @endif
                </div>
            @else
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg space-y-2">
                    <p class="font-semibold text-lg flex items-center gap-2">
                        <i class="fas fa-ban"></i> Brak ważnego certyfikatu
                    </p>
                    <p class="text-sm leading-snug">
                        Nie możesz podpisywać dokumentów, dopóki nie wygenerujesz certyfikatu.
                        Kliknij przycisk poniżej, aby przejść do zarządzania certyfikatem lub skontaktuj się z osobą wydającą.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('consultations.certificate.view') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-400 focus:outline-none transition">
                            <i class="fas fa-id-card"></i> Zarządzaj certyfikatem
                        </a>
                    </div>
                    <div class="text-sm text-gray-700 mt-3 leading-snug">
                        <p><strong>Certyfikaty wydaje:</strong></p>
                        <p>Ziemowit Gil — <a href="mailto:ziemowit.gil@feer.org.pl" class="text-blue-600 hover:underline">ziemowit.gil@feer.org.pl</a></p>
                        <p>lub <a href="mailto:certyfikaty@edukacja.cloud" class="text-blue-600 hover:underline">certyfikaty@edukacja.cloud</a></p>
                    </div>
                </div>
            @endif
        </section>

        {{-- KAFELKI AKCJI --}}
        <section aria-label="Szybkie akcje">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                @php
                    $tiles = [
                        ['route'=>'consultations.create', 'color'=>'blue', 'icon'=>'fa-stethoscope', 'label'=>'Nowa konsultacja'],
                        ['route'=>'schedules.create', 'color'=>'indigo', 'icon'=>'fa-calendar-plus', 'label'=>'Nowa rezerwacja'],
                        ['route'=>'clients.index', 'color'=>'green', 'icon'=>'fa-users', 'label'=>'Lista klientów'],
                        ['route'=>'raport', 'color'=>'gray', 'icon'=>'fa-file-alt', 'label'=>'Raporty'],
                        ['route'=>'consultations.certificate.view', 'color'=>'yellow', 'icon'=>'fa-certificate', 'label'=>'Zarządzaj certyfikatem'],
                    ];
                @endphp

                @foreach($tiles as $tile)
                    <a href="{{ route($tile['route']) }}"
                       class="group bg-gradient-to-br from-{{ $tile['color'] }}-50 to-white border border-{{ $tile['color'] }}-200 hover:shadow-lg rounded-2xl p-4 flex flex-col items-center transition focus:ring-2 focus:ring-{{ $tile['color'] }}-300 focus:outline-none"
                       aria-label="{{ $tile['label'] }}">
                        <div class="bg-{{ $tile['color'] }}-600 text-white rounded-full p-3 mb-2 shadow-sm">
                            <i class="fas {{ $tile['icon'] }}"></i>
                        </div>
                        <span class="text-gray-800 font-semibold text-sm group-hover:text-{{ $tile['color'] }}-700">{{ $tile['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- REZERWACJE --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- DZIŚ --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5" aria-label="Dzisiejsze rezerwacje">
                <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="fas fa-calendar-day text-blue-600"></i> Dzisiejsze rezerwacje
                </h2>

                @if($todaySchedules->isEmpty())
                    <p class="text-gray-500 text-sm">Brak zaplanowanych rezerwacji na dziś.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700">Godzina</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700">Klient</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($todaySchedules as $schedule)
                                <tr class="hover:bg-gray-50 border-b border-gray-100">
                                    <td class="px-3 py-2 text-gray-800">{{ $schedule->start_time->format('H:i') }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $schedule->client->name ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($schedule->status_label === 'Zatwierdzony') bg-green-100 text-green-800
                                            @elseif($schedule->status_label === 'Anulowany') bg-red-100 text-red-800
                                            @else bg-blue-100 text-blue-800 @endif">
                                            {{ $schedule->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- NAJBLIŻSZY TYDZIEŃ --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5" aria-label="Rezerwacje na 7 dni">
                <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="fas fa-calendar-week text-green-600"></i> Najbliższe 7 dni
                </h2>

                @if($weekSchedules->isEmpty())
                    <p class="text-gray-500 text-sm">Brak zaplanowanych rezerwacji w tym tygodniu.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700">Data</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700">Godzina</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700">Klient</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($weekSchedules as $schedule)
                                <tr class="hover:bg-gray-50 border-b border-gray-100">
                                    <td class="px-3 py-2 text-gray-800">{{ $schedule->start_time->format('d.m.Y') }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $schedule->start_time->format('H:i') }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $schedule->client->name ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($schedule->status_label === 'Zatwierdzony') bg-green-100 text-green-800
                                            @elseif($schedule->status_label === 'Anulowany') bg-red-100 text-red-800
                                            @else bg-blue-100 text-blue-800 @endif">
                                            {{ $schedule->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
