@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-5xl">

        <h1 class="text-3xl font-bold mb-6 text-gray-900">Certyfikat użytkownika</h1>

        <div id="alert-container" aria-live="polite" class="mb-4"></div>

        @if($certExists && $certData)
            <div class="bg-white rounded shadow flex flex-col md:flex-row gap-6 p-6" role="region" aria-label="Dane certyfikatu">

                {{-- Lewa kolumna: dane certyfikatu --}}
                <div class="flex-1">
                    <h2 class="text-2xl font-semibold mb-4">Dane certyfikatu</h2>
                    <table class="w-full table-auto border-collapse">
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
                    </table>

                    @if($isTestCert)
                        <p class="text-yellow-600 mt-4">To jest certyfikat testowy (staging).</p>
                    @endif


                </div>

                {{-- Prawa kolumna: akcje --}}
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
            <div class="bg-yellow-100 p-4 rounded shadow mb-4">
                Brak certyfikatu. Możesz wygenerować nowy certyfikat.
            </div>
            <button id="generate-cert" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none" aria-label="Generuj certyfikat">
                Generuj certyfikat
            </button>
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

            // Generowanie certyfikatu
            const generateBtn = document.getElementById('generate-cert');
            if (generateBtn) {
                generateBtn.addEventListener('click', function () {
                    fetch('{{ route("consultations.certificate.generate") }}', {
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
                        .catch(() => showAlert('Błąd podczas generowania certyfikatu.', 'danger'));
                });
            }

            // Cofanie certyfikatu
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
                            if(data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Błąd podczas cofania certyfikatu.', 'danger'));
                });
            }

            // Pobranie certyfikatu
            const downloadBtn = document.getElementById('download-cert');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function () {
                    window.location.href = '{{ route("certificate.download") }}';
                });
            }
        });
    </script>
@endsection
