{{-- resources/views/Certificate/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-3xl space-y-6">

        <h1 class="text-3xl font-bold text-gray-900 mb-4">Zarządzanie certyfikatem</h1>

        {{-- Sekcja informacyjna --}}
        <div class="p-6 rounded-2xl bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 shadow-sm space-y-4">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-info-circle text-blue-600"></i> Co to jest certyfikat X.509?
            </h2>
            <p class="text-gray-700 leading-relaxed">
                Certyfikat X.509 to cyfrowy dokument potwierdzający Twoją tożsamość w systemie.
                Działa jak cyfrowy dowód tożsamości: pozwala na weryfikację podpisów elektronicznych i połączeń.
            </p>
        </div>

        {{-- Status certyfikatu --}}
        @if($certExists)
            @php
                $daysLeft = (strtotime($certData['valid_to']) - time()) / 86400;
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
                    <li><strong>Ważny do:</strong> {{ $certData['valid_to'] }} (pozostało {{ ceil($daysLeft) }} dni)</li>
                </ul>
            </div>
        @else
            <div class="p-6 rounded-2xl bg-red-50 border border-red-300 shadow-sm space-y-3">
                <h2 class="text-lg font-semibold text-red-800 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i> Brak certyfikatu
                </h2>
                <p class="text-gray-800">
                    Nie masz jeszcze certyfikatu X.509. Możesz go wygenerować w zakładce
                    <a href="{{ route('consultations.certificate.view') }}" class="text-blue-600 hover:underline font-medium">Zarządzaj certyfikatem</a>.
                </p>
                <p class="text-gray-700 text-sm">
                    Certyfikaty wydaje: Ziemowit Gil (<a href="mailto:ziemowit.gil@feer.org.pl" class="text-blue-600 hover:underline">ziemowit.gil@feer.org.pl</a>)
                    lub <a href="mailto:certyfikaty@edukacja.cloud" class="text-blue-600 hover:underline">certyfikaty@edukacja.cloud</a>.
                </p>
            </div>

            {{-- Formularz generowania certyfikatu tylko jeśli brak certyfikatu --}}
            <div class="p-6 rounded-2xl bg-blue-50 border border-blue-300 shadow-sm space-y-3">
                <h2 class="text-lg font-semibold text-blue-800 flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Generowanie nowego certyfikatu
                </h2>
                <input type="password" id="certPassword" placeholder="Hasło (min. 6 znaków)"
                       class="border border-blue-300 p-2 rounded w-full focus:ring-2 focus:ring-blue-300 focus:outline-none">
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Generuj certyfikat</button>
                <p class="text-sm text-gray-600">Hasło zostanie użyte do zabezpieczenia certyfikatu.</p>
            </div>
        @endif

        {{-- Akcje certyfikatu --}}
        @if($certExists)
            <div class="p-6 rounded-2xl bg-gray-50 border border-gray-300 shadow-sm flex flex-wrap gap-3">
                <a href="{{ route('consultations.certificate.download') }}"
                   class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2">
                    <i class="fas fa-download"></i> Pobierz certyfikat
                </a>
                <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 flex items-center gap-2">
                    <i class="fas fa-times"></i> Cofnij certyfikat
                </button>
            </div>
        @endif

    </div>
@endsection
