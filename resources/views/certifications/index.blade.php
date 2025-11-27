@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        <section class="bg-white rounded-2xl shadow p-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Panel certyfikatów X.509</h1>
                <p class="text-gray-600 text-sm">Zarządzaj swoimi certyfikatami i kluczami prywatnymi.</p>
            </div>
            <div>
                <a href="{{ route('certificates.generate') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                    <i class="fas fa-plus-circle"></i> Wygeneruj nowy certyfikat
                </a>
            </div>
        </section>

        {{-- Lista certyfikatów użytkownika --}}
        <section class="bg-white rounded-2xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Twój certyfikat</h2>

            @php
                $userId = Auth::user()->id;
                $certFile = storage_path("app/certifications/{$userId}_cert.pem");
                $keyFile = storage_path("app/certifications/{$userId}_key.pem");
                $certExists = file_exists($certFile) && is_readable($certFile);
                $certInfo = null;
                $validUntil = null;

                if ($certExists) {
                    try {
                        $certContent = file_get_contents($certFile);
                        if ($certContent) {
                            $certInfo = openssl_x509_parse($certContent);
                            if (isset($certInfo['validTo_time_t'])) {
                                $validUntil = date('d.m.Y', $certInfo['validTo_time_t']);
                            }
                        } else {
                            $certExists = false;
                        }
                    } catch (\Exception $e) {
                        $certExists = false;
                    }
                }
            @endphp

            @if($certExists && $certInfo)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <div>
                        <p><strong>CN:</strong> {{ $certInfo['subject']['CN'] ?? 'Nieznany' }}</p>
                        <p><strong>O:</strong> {{ $certInfo['subject']['O'] ?? '-' }}</p>
                        <p><strong>Ważny do:</strong> {{ $validUntil ?? '-' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('certificates.download', ['userId' => $userId, 'type' => 'cert']) }}"
                           class="px-3 py-1 bg-green-100 text-green-800 rounded-xl hover:bg-green-200 transition">Pobierz certyfikat</a>
                        <a href="{{ route('certificates.download', ['userId' => $userId, 'type' => 'key']) }}"
                           class="px-3 py-1 bg-teal-100 text-teal-800 rounded-xl hover:bg-teal-200 transition">Pobierz klucz</a>
                    </div>
                    <div>
                        <form method="POST" action="{{ route('certificates.revoke', ['userId' => $userId]) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1 bg-red-100 text-red-800 rounded-xl hover:bg-red-200 transition"
                                    onclick="return confirm('Czy na pewno chcesz cofnąć certyfikat?')">Cofnij certyfikat</button>
                        </form>
                    </div>
                </div>
            @else
                <p class="text-gray-500">Nie posiadasz jeszcze certyfikatu. Wygeneruj go przyciskiem powyżej.</p>
            @endif
        </section>
    </div>
@endsection
