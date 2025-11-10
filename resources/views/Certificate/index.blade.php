@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-3xl space-y-6">

        <h1 class="text-3xl font-bold text-gray-900 mb-6">Zarządzanie certyfikatem</h1>

        {{-- Sekcja informacyjna o certyfikacie --}}
        <div class="p-5 rounded-2xl bg-gray-50 border border-gray-200 shadow-sm space-y-3">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-info-circle text-blue-600"></i> Certyfikat X.509 – co to jest?
            </h2>
            <p class="text-gray-700 leading-relaxed">
                Certyfikat X.509 to cyfrowy dokument potwierdzający Twoją tożsamość w systemie.
                Działa jak cyfrowy dowód tożsamości i pozwala weryfikować podpisy elektroniczne.
            </p>
            <p class="text-gray-700 leading-relaxed">
                Każdy certyfikat zawiera informacje o właścicielu:
            </p>
            <ul class="list-disc pl-5 text-gray-700 space-y-1">
                <li>Imię i nazwisko / nazwa organizacji</li>
                <li>Adres e-mail</li>
                <li>Jednostka organizacyjna</li>
                <li>Daty ważności certyfikatu</li>
                <li>Unikalny identyfikator SHA1 lub fingerprint</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">
                Certyfikat X.509 umożliwia bezpieczne podpisywanie dokumentów i szyfrowanie danych.
            </p>
            <p class="text-red-600 font-semibold">
                ⚠️ Nie próbuj generować ani modyfikować certyfikatu, jeśli nie wiesz jak go używać.
            </p>
        </div>

        {{-- Status certyfikatu --}}
        @if($certExists)
            @php
                $daysLeft = isset($certData['valid_to']) ? ceil((strtotime($certData['valid_to']) - time())/86400) : null;
            @endphp
            <div class="p-6 rounded-2xl bg-green-50 border border-green-300 shadow-sm space-y-3">
                <h2 class="text-lg font-semibold text-green-800 flex items-center gap-2">
                    <i class="fas fa-certificate"></i> Certyfikat aktywny {{ $isTestCert ? '(TESTOWY)' : '' }}
                </h2>
                <ul class="text-gray-800 space-y-1">
                    <li><strong>Imię i nazwisko:</strong> {{ $certData['common_name'] }}</li>
                    <li><strong>Email:</strong> {{ $certData['email'] }}</li>
                    <li><strong>Organizacja:</strong> {{ $certData['organization'] }}</li>
                    <li><strong>Jednostka organizacyjna:</strong> {{ $certData['organizational_unit'] ?? '-' }}</li>
                    <li><strong>SHA1:</strong> {{ $certData['sha1'] }}</li>
                    <li><strong>Ważny od:</strong> {{ $certData['valid_from'] }}</li>
                    <li><strong>Ważny do:</strong> {{ $certData['valid_to'] }}
                        @if($daysLeft !== null)
                            (pozostało {{ $daysLeft }} dni)
                        @endif
                    </li>
                </ul>
            </div>
        @else
            <div class="p-6 rounded-2xl bg-red-50 border border-red-300 shadow-sm space-y-3">
                <h2 class="text-lg font-semibold text-red-800 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i> Brak certyfikatu
                </h2>
                <p class="text-gray-800 leading-relaxed">
                    Nie możesz podpisywać dokumentów. Możesz wygenerować certyfikat w zakładce
                    <a href="{{ route('consultations.certificate.view') }}" class="text-blue-600 hover:underline font-medium">Zarządzaj certyfikatem</a>.
                </p>
                <p class="text-gray-700 text-sm leading-relaxed">
                    Certyfikaty wydaje Ziemowit Gil (<a href="mailto:ziemowit.gil@feer.org.pl" class="text-blue-600 hover:underline">ziemowit.gil@feer.org.pl</a>)
                    lub <a href="mailto:certyfikaty@edukacja.cloud" class="text-blue-600 hover:underline">certyfikaty@edukacja.cloud</a>.
                    Możesz go jednak samodzielnie wygenerować w zakładce powyżej.
                </p>
            </div>
        @endif

        {{-- Formularz generowania certyfikatu (tylko jeśli brak certyfikatu) --}}
        @if(!$certExists)
            <div class="p-6 rounded-2xl bg-blue-50 border border-blue-300 shadow-sm space-y-3">
                <h2 class="text-lg font-semibold text-blue-800 flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Generowanie nowego certyfikatu
                </h2>
                <input type="password" id="certPassword" placeholder="Hasło (min. 6 znaków)"
                       class="border border-blue-300 p-2 rounded w-full focus:ring-2 focus:ring-blue-300 focus:outline-none">
                <button id="generateCert" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center gap-2">
                    <i class="fas fa-key"></i> Generuj certyfikat
                </button>
                <div id="certMessage" class="text-sm text-gray-700 mt-2"></div>
            </div>
        @endif

        {{-- Akcje certyfikatu --}}
        @if($certExists)
            <div class="p-6 rounded-2xl bg-gray-50 border border-gray-300 shadow-sm flex flex-wrap gap-3">
                <a href="{{ route('consultations.certificate.download') }}"
                   class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2">
                    <i class="fas fa-download"></i> Pobierz certyfikat
                </a>
                <button id="revokeCert" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 flex items-center gap-2">
                    <i class="fas fa-times"></i> Cofnij certyfikat
                </button>
            </div>
        @endif

    </div>
@endsection
