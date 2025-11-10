@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-6 py-8" role="main" aria-label="Szczegóły certyfikatu użytkownika">

        <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 space-y-4" aria-label="Informacje o certyfikacie">
            <h1 class="text-xl font-semibold text-gray-800">Certyfikat użytkownika</h1>

            @if($certData)
                <p class="text-gray-600 text-sm">Szczegóły Twojego certyfikatu:</p>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-700">
                    <dt class="font-medium">Common Name (CN)</dt>
                    <dd>{{ $certData['common_name'] ?? '-' }}</dd>

                    <dt class="font-medium">Email</dt>
                    <dd>{{ $certData['email'] ?? '-' }}</dd>

                    <dt class="font-medium">Organizacja (O)</dt>
                    <dd>{{ $certData['organization'] ?? '-' }}</dd>

                    <dt class="font-medium">Jednostka organizacyjna (OU)</dt>
                    <dd>{{ $certData['organizational_unit'] ?? '-' }}</dd>

                    <dt class="font-medium">Data ważności od</dt>
                    <dd>{{ $certData['valid_from'] ?? '-' }}</dd>

                    <dt class="font-medium">Data ważności do</dt>
                    <dd>{{ $certData['valid_to'] ?? '-' }}</dd>

                    <dt class="font-medium">SHA1</dt>
                    <dd class="break-all">{{ $certData['sha1'] ?? '-' }}</dd>
                </dl>

                @if($isTestCert)
                    <p class="mt-4 text-yellow-800 font-semibold">Certyfikat testowy (ważny 6 godzin, środowisko staging)</p>
                @endif
            @else
                <p class="text-gray-500">Brak certyfikatu dla Twojego konta. Możesz go wygenerować lub zaimportować.</p>
            @endif

            <a href="{{ route('consultations.index') }}" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm" aria-label="Powrót do dashboardu">
                Powrót do dashboardu
            </a>
        </section>

    </div>
@endsection
