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

    <div class="container mx-auto px-6 py-10 space-y-8 bg-gray-50 min-h-screen" role="main">

        {{-- ALERT REDIS --}}
        @if($redisStatus !== 'Dostępny')
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex justify-between items-center" role="alert" aria-live="assertive">
                <span><strong>Uwaga:</strong> {{ $redisStatus }} — system może działać wolniej.</span>
                <button onclick="this.parentElement.remove()" aria-label="Zamknij alert" class="ml-3 text-red-600 font-bold text-lg focus:outline-none focus:ring-2 focus:ring-red-400">&times;</button>
            </div>
        @endif

        {{-- ALERTY SESJI --}}
        @foreach (['warning','error','success'] as $msg)
            @if(session($msg))
                @php $colors = ['warning'=>'yellow','error'=>'red','success'=>'green']; @endphp
                <div class="p-4 bg-{{ $colors[$msg] }}-50 border border-{{ $colors[$msg] }}-200 text-{{ $colors[$msg] }}-800 rounded-xl" role="alert" aria-live="polite">
                    {{ session($msg) }}
                </div>
            @endif
        @endforeach

        {{-- KAFELKI AKCJI --}}
        <section aria-label="Szybkie akcje">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-6">
                @php
                    $tiles = [
                        ['route'=>'consultations.create', 'color'=>'blue', 'icon'=>'fa-solid fa-user-doctor', 'label'=>'Nowa konsultacja', 'desc'=>'Zarejestruj konsultację dla klienta'],
                        ['route'=>'schedules.create', 'color'=>'indigo', 'icon'=>'fa-solid fa-calendar-plus', 'label'=>'Nowa rezerwacja', 'desc'=>'Dodaj nową rezerwację w harmonogramie'],
                        ['route'=>'clients.index', 'color'=>'green', 'icon'=>'fa-solid fa-users', 'label'=>'Lista klientów', 'desc'=>'Przeglądaj i zarządzaj klientami'],
                        ['route'=>'raport', 'color'=>'gray', 'icon'=>'fa-solid fa-chart-line', 'label'=>'Raporty', 'desc'=>'Generuj raporty systemowe'],
                        ['route'=>'consultations.certificate.view', 'color'=>'yellow', 'icon'=>'fa-solid fa-certificate', 'label'=>'Zarządzaj certyfikatem', 'desc'=>'Twórz i przeglądaj certyfikaty'],
                    ];
                @endphp

                @foreach($tiles as $tile)
                    <a href="{{ route($tile['route']) }}"
                       class="group bg-white border border-gray-200 rounded-2xl p-6 flex flex-col items-start transition transform hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-{{ $tile['color'] }}-300"
                       aria-label="{{ $tile['label'] }} - {{ $tile['desc'] }}">
                        <div class="bg-gradient-to-r from-{{ $tile['color'] }}-500 to-{{ $tile['color'] }}-700 text-white rounded-xl p-4 mb-4 shadow-md flex items-center justify-center w-14 h-14">
                            <i class="{{ $tile['icon'] }} text-xl"></i>
                        </div>
                        <div class="space-y-1">
                            <span class="text-gray-900 font-semibold group-hover:text-{{ $tile['color'] }}-700">{{ $tile['label'] }}</span>
                            <p class="text-gray-500 text-xs">{{ $tile['desc'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- CERTYFIKAT --}}
        <section class="bg-white rounded-2xl shadow-lg p-6 mt-6 flex flex-col md:flex-row items-start md:items-center space-x-0 md:space-x-6 space-y-4 md:space-y-0 hover:bg-yellow-50 transition" aria-label="Informacje o certyfikacie">
            <div class="bg-yellow-100 p-4 rounded-full text-yellow-600 flex-shrink-0">
                <i class="fas fa-certificate fa-2x"></i>
            </div>
            <div class="flex-1 space-y-2">
                @if($certExists)
                    <p class="font-semibold text-gray-900">CN: {{ $certCN }}</p>
                    <p class="text-gray-700">Organizacja: {{ $certOrg }}</p>
                    <p class="text-gray-700">Ważny do: {{ $certValidUntil }}</p>
                    @if($certExpiringSoon)
                        <div class="p-3 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg mt-2 flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            Twój certyfikat wkrótce wygaśnie. Skontaktuj się z administratorem w celu odnowienia.
                        </div>
                    @endif
                    <p class="text-gray-500 text-sm mt-1 italic">
                        W wersji produkcyjnej do wydania certyfikatu wymagane będzie podanie dokumentu potwierdzającego kwalifikacje zawodowe.
                    </p>
                @else
                    <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg space-y-2">
                        <p class="font-semibold text-lg flex items-center gap-2">
                            <i class="fas fa-ban"></i> Brak ważnego certyfikatu
                        </p>
                        <p class="text-gray-700 text-sm leading-snug">
                            Nie możesz podpisywać dokumentów, dopóki nie wygenerujesz certyfikatu.
                            Mimo że certyfikaty wydaje Ziemowit Gil, możesz go samodzielnie wygenerować w zakładce
                            <a href="{{ route('consultations.certificate.view') }}" class="text-blue-600 hover:underline">Zarządzaj certyfikatem</a>.
                        </p>

                        <div class="text-sm text-gray-700 mt-3 leading-snug">
                            <p><strong>W przypadku problemów:</strong></p>
                            <p>Ziemowit Gil — <a href="mailto:ziemowit.gil@feer.org.pl" class="text-blue-600 hover:underline">ziemowit.gil@feer.org.pl</a></p>
                            <p>lub <a href="mailto:certyfikaty@edukacja.cloud" class="text-blue-600 hover:underline">certyfikaty@edukacja.cloud</a></p>
                        </div>

                        <p class="text-gray-500 text-sm mt-1 italic">
                            W wersji produkcyjnej wymagane będzie podanie dokumentu potwierdzającego posiadanie kwalifikacji zawodowych.
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- REZERWACJE --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

            {{-- DZIS --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="fas fa-calendar-day text-blue-600"></i> Dzisiejsze rezerwacje
                </h2>
                @if($todaySchedules->isEmpty())
                    <p class="text-gray-500 text-sm">Brak zaplanowanych rezerwacji na dziś.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700 uppercase">Godzina</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700 uppercase">Klient</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700 uppercase">Status</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            @foreach($todaySchedules as $schedule)
                                <tr class="hover:bg-gray-50">
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
            <div class="bg-white border border-gray-200 rounded-2xl shadow p-5">
                <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="fas fa-calendar-week text-green-600"></i> Najbliższe 7 dni
                </h2>
                @if($weekSchedules->isEmpty())
                    <p class="text-gray-500 text-sm">Brak zaplanowanych rezerwacji w tym tygodniu.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700 uppercase">Data</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700 uppercase">Godzina</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700 uppercase">Klient</th>
                                <th class="text-left px-3 py-2 font-semibold text-gray-700 uppercase">Status</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            @foreach($weekSchedules as $schedule)
                                <tr class="hover:bg-gray-50">
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
