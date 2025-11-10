@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-4xl">

        <h1 class="text-3xl font-bold mb-6 text-gray-900">Certyfikat użytkownika</h1>

        <div id="alert-container" aria-live="polite" class="mb-4"></div>

        @php
            $certDir = storage_path('app/certificates');
            $certPath = $certDir."/{$user->id}_user_cert.crt";
            $keyPath  = $certDir."/{$user->id}_user_key.key";
            $certExists = file_exists($certPath);
            $certData = null;
            $isTestCert = false;
            if ($certExists) {
                $certContent = file_get_contents($certPath);
                $parsed = @openssl_x509_parse(openssl_x509_read($certContent));
                if ($parsed) {
                    $certData = [
                        'common_name' => $parsed['subject']['CN'] ?? null,
                        'email' => $parsed['subject']['emailAddress'] ?? null,
                        'organization' => $parsed['subject']['O'] ?? null,
                        'organizational_unit' => $parsed['subject']['OU'] ?? null,
                        'valid_from' => isset($parsed['validFrom_time_t']) ? date('Y-m-d', $parsed['validFrom_time_t']) : null,
                        'valid_to' => isset($parsed['validTo_time_t']) ? date('Y-m-d', $parsed['validTo_time_t']) : null,
                        'sha1' => sha1($certContent),
                        'authorized_by' => 'Ziemowit Gil (FEER)'
                    ];
                    $isTestCert = app()->environment('staging') && (time() - filemtime($certPath) < 6*3600);
                } else {
                    $certExists = false;
                }
            }
        @endphp

        @if($certExists && $certData)
            <div class="bg-white rounded shadow p-6">

                <h2 class="text-2xl font-semibold mb-4">Dane certyfikatu</h2>
                <table class="w-full table-auto border-collapse mb-6">
                    <tbody>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Imię i nazwisko</td>
                        <td class="py-2 px-4 border-b">{{ $certData['common_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Email</td>
                        <td class="py-2 px-4 border-b">{{ $certData['email'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Organizacja</td>
                        <td class="py-2 px-4 border-b">{{ $certData['organization'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Jednostka organizacyjna</td>
                        <td class="py-2 px-4 border-b">{{ $certData['organizational_unit'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Autoryzował</td>
                        <td class="py-2 px-4 border-b">{{ $certData['authorized_by'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Ważny od</td>
                        <td class="py-2 px-4 border-b">{{ $certData['valid_from'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Ważny do</td>
                        <td class="py-2 px-4 border-b">{{ $certData['valid_to'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">SHA1</td>
                        <td class="py-2 px-4 border-b font-mono break-all">{{ $certData['sha1'] }}</td>
                    </tr>
                    </tbody>
                </table>

                <p class="text-gray-700 mb-4">
                    Certyfikat będzie służył do podpisywania dokumentacji w systemie. Ścieżka certyfikacji: <br>
                    <strong>Krajowa Izba Rozliczeniowa -> UMWM -> FEER -> {{ $user->name }}</strong>
                </p>

                <div class="flex flex-col md:flex-row gap-3">
                    <button id="download-cert" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:ring-2 focus:ring-green-300 focus:outline-none">
                        Pobierz certyfikat
                    </button>
                    <button id="revoke-cert" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none">
                        Cofnij certyfikat
                    </button>
                </div>

            </div>
        @else
            <div class="bg-yellow-100 p-4 rounded shadow mb-4">
                Brak certyfikatu. Możesz wygenerować nowy certyfikat.
            </div>

            <div class="bg-white rounded shadow p-6">
                <h2 class="text-2xl font-semibold mb-4">Dane do certyfikatu</h2>
                <p class="text-gray-700 mb-2">Imię i nazwisko: <strong>{{ $user->name }}</strong></p>
                <p class="text-gray-700 mb-2">Email: <strong>{{ $user->email }}</strong></p>
                <p class="text-gray-700 mb-2">Organizacja: <strong>FEER</strong></p>
                <p class="text-gray-700 mb-2">Jednostka organizacyjna: <strong>Certyfikaty podpisu dokumentacji</strong></p>
                <p class="text-gray-700 mb-4">Autoryzował: <strong>Ziemowit Gil (FEER)</strong></p>

                <p class="text-gray-600 mb-4 text-sm">
                    Wprowadź hasło do certyfikatu – będzie potrzebne przy podpisywaniu dokumentów. Użyj hasła trudnego do odgadnięcia (min. 6 znaków), najlepiej zawierającego litery i cyfry.
                </p>

                <div class="flex flex-col md:flex-row gap-3 items-start">
                    <input type="password" id="cert-password" placeholder="Hasło do certyfikatu" class="px-4 py-2 border rounded focus:ring-2 focus:ring-blue-300 focus:outline-none w-full md:w-1/3">
                    <button id="generate-cert" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none">
                        Generuj certyfikat
                    </button>
                </div>

                @if(!file_exists($certDir))
                    <p class="text-red-600 mt-2">Uwaga: katalog certyfikatów nie istnieje. System spróbuje go utworzyć.</p>
                @endif
            </div>
        @endif

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const token = '{{ csrf_token() }}';

            function showAlert(message, type='info') {
                const container = document.getElementById('alert-container');
                container.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
            }

            // Generowanie certyfikatu
            const generateBtn = document.getElementById('generate-cert');
            if(generateBtn){
                generateBtn.addEventListener('click', function(){
                    const password = document.getElementById('cert-password').value.trim();
                    if(!password) { showAlert('Podaj hasło do certyfikatu', 'warning'); return; }

                    fetch('{{ route("consultations.certificate.generate") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ password })
                    })
                        .then(res => res.json())
                        .then(data => {
                            showAlert(data.message, data.success ? 'success' : 'danger');
                            if(data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Błąd podczas generowania certyfikatu.', 'danger'));
                });
            }

            // Cofanie certyfikatu
            const revokeBtn = document.getElementById('revoke-cert');
            if(revokeBtn){
                revokeBtn.addEventListener('click', function(){
                    if(!confirm('Czy na pewno chcesz cofnąć certyfikat?')) return;

                    fetch('{{ route("consultations.certificate.revoke") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            showAlert(data.message, data.success ? 'success' : 'danger');
                            if(data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Błąd podczas cofania certyfikatu.', 'danger'));
                });
            }

            // Pobranie certyfikatu
            const downloadBtn = document.getElementById('download-cert');
            if(downloadBtn){
                downloadBtn.addEventListener('click', function(){
                    window.location.href = '{{ route("consultations.certificate.download") }}';
                });
            }
        });
    </script>
@endsection
