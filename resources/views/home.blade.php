@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- ALERT REDIS --}}
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
                <div class="p-4 bg-{{ $colors[$msg] }}-50 border border-{{ $colors[$msg] }}-200 text-{{ $colors[$msg] }}-800 rounded-xl" role="alert">
                    {{ session($msg) }}
                </div>
            @endif
        @endforeach

        {{-- SZYBKIE AKCJE --}}
        <section aria-label="Szybkie akcje" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @php
                $tiles = [
                    ['route'=>'consultations.create', 'color'=>'blue', 'icon'=>'fa-stethoscope', 'label'=>'Nowa konsultacja'],
                    ['route'=>'schedules.create', 'color'=>'indigo', 'icon'=>'fa-calendar-plus', 'label'=>'Nowa rezerwacja'],
                    ['route'=>'clients.create', 'color'=>'green', 'icon'=>'fa-user-plus', 'label'=>'Dodaj klienta'],
                    ['route'=>'clients.index', 'color'=>'teal', 'icon'=>'fa-users', 'label'=>'Lista klientów'],
                    ['route'=>'raport', 'color'=>'gray', 'icon'=>'fa-file-alt', 'label'=>'Raporty'],
                    ['route'=>'consultations.certificate.view', 'color'=>'yellow', 'icon'=>'fa-certificate', 'label'=>'Certyfikat'],
                ];
            @endphp

            @foreach($tiles as $tile)
                <a href="{{ route($tile['route']) }}"
                   class="group relative bg-gradient-to-br from-{{ $tile['color'] }}-50 to-white border border-{{ $tile['color'] }}-200 hover:shadow-lg rounded-2xl p-4 flex flex-col items-center transition focus:outline-none focus:ring-4 focus:ring-{{ $tile['color'] }}-300"
                   aria-label="{{ $tile['label'] }}">
                    <div class="bg-{{ $tile['color'] }}-600 text-white rounded-full p-4 mb-2 shadow-sm flex items-center justify-center w-16 h-16 text-2xl">
                        <i class="fas {{ $tile['icon'] }}"></i>
                    </div>
                    <span class="text-gray-900 font-semibold text-sm text-center group-hover:text-{{ $tile['color'] }}-700">{{ $tile['label'] }}</span>
                </a>
            @endforeach
        </section>

        {{-- INFORMACJA O PRODUKCJI / CERTYFIKACIE --}}
        <section class="bg-white rounded-2xl shadow p-6" aria-label="Informacje o certyfikacie">
            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle text-yellow-600"></i> Informacje o certyfikacie
            </h2>
            <p class="text-gray-700 leading-snug">
                W <strong>wersji produkcyjnej</strong> do wydania certyfikatu oraz pełnej obsługi systemu będzie wymagane podanie danych dokumentu potwierdzającego kwalifikacje.
                Mimo że certyfikaty wydaje Ziemowit Gil, możesz samodzielnie wygenerować certyfikat w zakładce
                <a href="{{ route('consultations.certificate.view') }}" class="text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-400">Zarządzaj certyfikatem</a>.
            </p>
        </section>

        {{-- REZERWACJE --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- DZISIEJSZE REZERWACJE --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow p-5">
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
            <div class="bg-white border border-gray-200 rounded-2xl shadow p-5">
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
