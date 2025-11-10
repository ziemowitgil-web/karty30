@extends('layouts.app')

@section('content')
    @php
        $certDir = storage_path('app/certificates');
        $certFile = $certDir . '/' . Auth::user()->id . '_user_cert.pem';
        $certExists = file_exists($certFile);

        $certCN = 'Brak certyfikatu';
        $certValidUntil = null;
        $certExpiringSoon = false;

        if ($certExists) {
            $certContent = file_get_contents($certFile);
            $certInfo = openssl_x509_parse($certContent);
            $certCN = $certInfo['subject']['CN'] ?? 'Nieznany';
            if (isset($certInfo['validTo_time_t'])) {
                $certValidUntil = date('d.m.Y', $certInfo['validTo_time_t']);
                $daysLeft = ($certInfo['validTo_time_t'] - time()) / 86400;
                $certExpiringSoon = $daysLeft <= 10;
            }
        }
    @endphp

    <div class="container mx-auto px-6 py-8 space-y-6" role="main" aria-label="Panel główny użytkownika">

        {{-- ALERTY SYSTEMOWE --}}
        @if($redisStatus !== 'Dostępny')
            <div class="p-4 bg-red-100 border border-red-300 text-red-800 rounded-xl flex justify-between items-center" role="alert" aria-live="assertive">
                <span>{{ $redisStatus }} — system może działać wolniej.</span>
                <button onclick="this.parentElement.remove()" aria-label="Zamknij alert" class="ml-3 text-red-600 font-bold text-lg">&times;</button>
            </div>
        @endif

        @foreach (['warning','error','success'] as $msg)
            @if(session($msg))
                @php $colors = ['warning'=>'yellow','error'=>'red','success'=>'green']; @endphp
                <div class="p-4 bg-{{ $colors[$msg] }}-100 border border-{{ $colors[$msg] }}-300 text-{{ $colors[$msg] }}-800 rounded-xl" role="status">
                    {{ session($msg) }}
                </div>
            @endif
        @endforeach

        {{-- CERTYFIKAT --}}
        <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 space-y-3">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-id-card text-blue-500"></i> Twój certyfikat
            </h2>

            @if($certExists)
                <div class="text-gray-700 space-y-1">
                    <p><span class="font-semibold text-gray-900">CN:</span> {{ $certCN }}</p>
                    <p><span class="font-semibold text-gray-900">Ważny do:</span> {{ $certValidUntil }}</p>
                    @if($certExpiringSoon)
                        <div class="p-3 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Certyfikat wkrótce wygaśnie – prosimy o odnowienie.
                        </div>
                    @endif
                </div>
            @else
                <div class="p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">
                    <p class="font-semibold">Brak ważnego certyfikatu.</p>
                    <p class="text-sm">Nie możesz podpisywać dokumentów, dopóki nie wygenerujesz nowego certyfikatu.</p>
                </div>
            @endif

            <div class="text-sm text-gray-600 mt-2">
                <p class="font-medium text-gray-700">Certyfikaty wydaje:</p>
                <p class="text-gray-700">Ziemowit Gil — <a href="mailto:ziemowit.gil@feer.org.pl" class="text-blue-600 hover:underline">ziemowit.gil@feer.org.pl</a></p>
                <p>lub <a href="mailto:certyfikaty@edukacja.cloud" class="text-blue-600 hover:underline">certyfikaty@edukacja.cloud</a></p>
            </div>
        </section>

        {{-- KAFELKI AKCJI --}}
        <section aria-label="Szybkie akcje">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <a href="{{ route('consultations.create') }}" class="group bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 hover:shadow-lg rounded-2xl p-4 flex flex-col items-center transition">
                    <div class="bg-blue-600 text-white rounded-full p-3 mb-2"><i class="fas fa-stethoscope"></i></div>
                    <span class="text-gray-800 font-semibold text-sm group-hover:text-blue-800">Nowa konsultacja</span>
                </a>

                <a href="{{ route('schedules.create') }}" class="group bg-gradient-to-br from-indigo-50 to-indigo-100 border border-indigo-200 hover:shadow-lg rounded-2xl p-4 flex flex-col items-center transition">
                    <div class="bg-indigo-600 text-white rounded-full p-3 mb-2"><i class="fas fa-calendar-plus"></i></div>
                    <span class="text-gray-800 font-semibold text-sm group-hover:text-indigo-800">Nowa rezerwacja</span>
                </a>

                <a href="{{ route('clients.index') }}" class="group bg-gradient-to-br from-green-50 to-green-100 border border-green-200 hover:shadow-lg rounded-2xl p-4 flex flex-col items-center transition">
                    <div class="bg-green-600 text-white rounded-full p-3 mb-2"><i class="fas fa-users"></i></div>
                    <span class="text-gray-800 font-semibold text-sm group-hover:text-green-800">Lista klientów</span>
                </a>

                <a href="{{ route('raport') }}" class="group bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 hover:shadow-lg rounded-2xl p-4 flex flex-col items-center transition">
                    <div class="bg-gray-700 text-white rounded-full p-3 mb-2"><i class="fas fa-file-alt"></i></div>
                    <span class="text-gray-800 font-semibold text-sm group-hover:text-gray-900">Raporty</span>
                </a>

                <a href="{{ route('consultations.certificate.view') }}" class="group bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 hover:shadow-lg rounded-2xl p-4 flex flex-col items-center transition">
                    <div class="bg-yellow-500 text-white rounded-full p-3 mb-2"><i class="fas fa-shield-alt"></i></div>
                    <span class="text-gray-800 font-semibold text-sm group-hover:text-yellow-800">Zarządzaj certyfikatem</span>
                </a>
            </div>
        </section>

        {{-- REZERWACJE --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- DZIŚ --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-calendar-day text-blue-500"></i> Dzisiejsze rezerwacje
                </h2>

                @if($todaySchedules->isEmpty())
                    <p class="text-gray-500 text-sm">Brak zaplanowanych rezerwacji na dziś.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead class="bg-gray-50 text-gray-700 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-3 py-2 text-sm font-semibold">Godzina</th>
                                <th class="text-left px-3 py-2 text-sm font-semibold">Klient</th>
                                <th class="text-left px-3 py-2 text-sm font-semibold">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($todaySchedules as $schedule)
                                <tr class="hover:bg-gray-50 border-b border-gray-100">
                                    <td class="px-3 py-2 text-gray-800">{{ $schedule->start_time->format('H:i') }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $schedule->client->name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $schedule->status_label_color }}">
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
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-calendar-week text-green-500"></i> Najbliższe 7 dni
                </h2>

                @if($weekSchedules->isEmpty())
                    <p class="text-gray-500 text-sm">Brak zaplanowanych rezerwacji w tym tygodniu.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead class="bg-gray-50 text-gray-700 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-3 py-2 text-sm font-semibold">Data</th>
                                <th class="text-left px-3 py-2 text-sm font-semibold">Godzina</th>
                                <th class="text-left px-3 py-2 text-sm font-semibold">Klient</th>
                                <th class="text-left px-3 py-2 text-sm font-semibold">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($weekSchedules as $schedule)
                                <tr class="hover:bg-gray-50 border-b border-gray-100">
                                    <td class="px-3 py-2 text-gray-800">{{ $schedule->start_time->format('d.m.Y') }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $schedule->start_time->format('H:i') }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $schedule->client->name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $schedule->status_label_color }}">
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
