@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-5xl">

        <h1 class="text-3xl font-bold mb-6 text-gray-900">Twój certyfikat X.509</h1>

        {{-- Alerty --}}
        <div id="alert-container" aria-live="polite" class="mb-6"></div>

        {{-- Instrukcja dla użytkownika --}}
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded mb-6">
            <p class="text-blue-900 font-medium mb-2">Informacje o certyfikacie:</p>
            <ul class="list-disc list-inside text-blue-800">
                <li>Certyfikat służy do podpisu dokumentacji w systemie FEER.</li>
                <li>Certyfikat jest powiązany z Twoim kontem i zawiera dane kontaktowe.</li>
                <li>Autoryzowany przez: <strong>Ziemowit Gil (FEER)</strong></li>
                <li>Urząd certyfikacji (CA): <strong>FEER</strong></li>
                <li>Root-cert: <strong>Krajowa Izba Rozliczeniowa</strong></li>
            </ul>
        </div>

        @if($certExists && $certData)
            {{-- Certyfikat już wygenerowany --}}
            <div class="bg-white rounded shadow p-6 flex flex-col md:flex-row gap-6" role="region" aria-label="Dane certyfikatu">

                {{-- Dane certyfikatu --}}
                <div class="flex-1">
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
                        <tr>
                            <td class="font-medium py-2 px-4 border-b">Autoryzował</td>
                            <td class="py-2 px-4 border-b">Ziemowit Gil (FEER)</td>
                        </tr>
                        <tr>
                            <td class="font-medium py-2 px-4 border-b">Urząd certyfikacji (CA)</td>
                            <td class="py-2 px-4 border-b">FEER</td>
                        </tr>
                        <tr>
                            <td class="font-medium py-2 px-4 border-b">Root-cert</td>
                            <td class="py-2 px-4 border-b">Krajowa Izba Rozliczeniowa</td>
                        </tr>
                        </tbody>
                    </table>

                    @if($isTestCert)
                        <p class="text-yellow-600 mb-4">To jest certyfikat testowy (staging).</p>
                    @endif
                </div>

                {{-- Akcje --}}
                <div class="flex flex-col gap-3 md:w-64">
                    <button id="download-cert" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:ring-2 focus:ring-green-300 focus:outline-none" aria-label="Pobierz certyfikat">
                        Pobierz certyfikat
                    </button>
                    <button id="revoke-cert" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none" aria-label="Cofnij certyfikat">
                        Cofnij certyfikat
                    </button>
                </div>

            </div>

        @else
            {{-- Formularz generowania certyfikatu --}}
            <div class="bg-yellow-100 p-4 rounded shadow mb-4">
                Brak certyfikatu. Wprowadź hasło i wygeneruj nowy certyfikat.
            </div>

            <div class="bg-white rounded shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Dane, które trafią do certyfikatu</h2>
                <table class="w-full table-auto border-collapse mb-6">
                    <tbody>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Imię i nazwisko</td>
                        <td class="py-2 px-4 border-b">{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Email</td>
                        <td class="py-2 px-4 border-b">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Organizacja</td>
                        <td class="py-2 px-4 border-b">FEER</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Jednostka organizacyjna</td>
                        <td class="py-2 px-4 border-b">Certyfikaty podpisu niekwalifikowanego dokumentacji</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Autoryzował</td>
                        <td class="py-2 px-4 border-b">Ziemowit Gil (FEER)</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Urząd certyfikacji (CA)</td>
                        <td class="py-2 px-4 border-b">FEER</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Root-cert</td>
                        <td class="py-2 px-4 border-b">Krajowa Izba Rozliczeniowa</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Ważność certyfikatu</td>
                        <td class="py-2 px-4 border-b">180 dni</td>
                    </tr>
                    </tbody>
                </table>

                <div class="flex flex-col md:flex-row gap-3 items-start">
                    <input type="password" id="cert-password" placeholder="Hasło do certyfikatu" class="px-4 py-2 border rounded focus:ring-2 focus:ring-blue-300 focus:outline-none w-full md:w-1/3">
                    <button id="generate-cert" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none">
                        Generuj certyfikat
                    </button>
                </div>
            </div>
        @endif

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const token = '{{ csrf_token() }}';

            function showAlert(message, type = 'info') {
                const container = document.getElementById('alert-container');
                container.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
            }

            // GENERUJ CERTYFIKAT
            const generateBtn = document.getElementById('generate-cert');
            if (generateBtn) {
                generateBtn.addEventListener('click', function () {
                    const password = document.getElementById('cert-password').value.trim();
                    if (!password) { showAlert('Podaj hasło do certyfikatu.', 'warning'); return; }

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
                            if (data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Błąd podczas generowania certyfikatu.', 'danger'));
                });
            }

            // COFNIJ CERTYFIKAT
            const revokeBtn = document.getElementById('revoke-cert');
            if (revokeBtn) {
                revokeBtn.addEventListener('click', function () {
                    if (!confirm('Czy na pewno chcesz cofnąć certyfikat?')) return;

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
                            if (data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Błąd podczas cofania certyfikatu.', 'danger'));
                });
            }

            // POBIERZ CERTYFIKAT
            const downloadBtn = document.getElementById('download-cert');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function () {
                    window.location.href = '{{ route("consultations.certificate.download") }}';
                });
            }
        });
    </script>
@endsection
