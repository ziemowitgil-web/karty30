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

        {{-- REZERWACJE: Dzisiaj i Najbliższy tydzień --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @include('partials.home.today-schedules', ['todaySchedules' => $todaySchedules])
            @include('partials.home.week-schedules', ['weekSchedules' => $weekSchedules])
        </section>
    </div>
@endsection
