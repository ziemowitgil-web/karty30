{{-- resources/views/Certificate/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-3xl space-y-6">

    <h1 class="text-3xl font-bold text-gray-900 mb-4">Zarządzanie certyfikatem</h1>

    {{-- Sekcja informacyjna --}}
    <div class="p-5 rounded-2xl border border-gray-300 bg-gray-50 space-y-3">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
            <i class="fas fa-shield-alt text-blue-600"></i> Certyfikat X.509
        </h2>
        <p class="text-gray-700">
            Certyfikat X.509 to cyfrowy dokument potwierdzający Twoją tożsamość i umożliwiający podpis elektroniczny dokumentów.
        </p>
        <p class="text-gray-700">
            Każdy certyfikat zawiera m.in. CN, organizację, adres email i daty ważności.
        </p>
        <p class="text-red-600 font-semibold">
            ⚠️ Jeśli nie posiadasz certyfikatu, nie możesz podpisywać dokumentów.
        </p>
    </div>

    {{-- Status certyfikatu --}}
    @if($certExists)
    <div class="p-5 rounded-2xl border border-green-400 bg-green-50 space-y-2">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <i class="fas fa-check-circle text-green-600"></i> Certyfikat aktywny
        </h2>
        <ul class="text-gray-700 space-y-1">
            <li><strong>Posiadacz (CN):</strong> {{ $certData['common_name'] }}</li>
            <li><strong>Email:</strong> {{ $certData['email'] }}</li>
            <li><strong>Organizacja:</strong> {{ $certData['organization'] }}</li>
            <li><strong>Jednostka organizacyjna:</strong> {{ $certData['organizational_unit'] ?? '-' }}</li>
            <li><strong>SHA1:</strong> {{ $certData['sha1'] }}</li>
            <li><strong>Ważny od:</strong> {{ $certData['valid_from'] }}</li>
            <li><strong>Ważny do:</strong> {{ $certData['valid_to'] }}</li>
        </ul>

        <div class="flex flex-wrap gap-3 mt-3">
            <a href="{{ route('consultations.certificate.download') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-download"></i> Pobierz certyfikat
            </a>

            <button id="revokeCert"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-ban"></i> Cofnij certyfikat
            </button>

            {{-- Komunikaty JS --}}
            <div id="certMessage" class="w-full text-sm mt-2 text-gray-800"></div>
        </div>
    </div>
    @else
    <div class="p-5 rounded-2xl border border-gray-400 bg-gray-50 space-y-3">
        <h2 class="text-lg font-semibold text-red-600 flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> Brak certyfikatu
        </h2>
        <p class="text-gray-700">
            Możesz samodzielnie wygenerować certyfikat w zakładce poniżej. Jeśli napotkasz problemy, skontaktuj się z administratorem:
        </p>
        <ul class="text-gray-700 list-disc pl-5 space-y-1">
            <li>Ziemowit Gil — <a href="mailto:ziemowit.gil@feer.org.pl" class="text-blue-600 hover:underline">ziemowit.gil@feer.org.pl</a></li>
            <li>lub <a href="mailto:certyfikaty@edukacja.cloud" class="text-blue-600 hover:underline">certyfikaty@edukacja.cloud</a></li>
        </ul>

        <a href="{{ route('consultations.certificate.view') }}"
           class="inline-flex items-center gap-2 px-4 py-2 mt-3 bg-yellow-500 text-white font-medium rounded-lg hover:bg-yellow-600 transition">
            <i class="fas fa-id-card"></i> Zarządzaj certyfikatem
        </a>
    </div>
    @endif

    {{-- Formularz generowania certyfikatu --}}
    @unless($certExists)
    <div class="p-5 rounded-2xl border border-blue-400 bg-blue-50 space-y-3">
        <h2 class="font-semibold text-lg mb-2 flex items-center gap-2">
            <i class="fas fa-plus-circle text-blue-600"></i> Generowanie nowego certyfikatu
        </h2>
        <input type="password" id="certPassword" placeholder="Hasło (min. 6 znaków)"
               class="border p-2 rounded w-full">
        <button id="generateCert"
                class="mt-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
            Generuj certyfikat
        </button>
        <div id="certMessage" class="text-sm mt-2"></div>
    </div>
    @endunless
</div>
@endsection
