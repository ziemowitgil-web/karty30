@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-4xl">

        <h1 class="text-3xl font-bold mb-6 text-gray-900">Certyfikat użytkownika</h1>

        {{-- Alerty AJAX --}}
        <div id="alert-container" aria-live="polite" class="mb-4"></div>

        {{-- Certyfikat --}}
        <div id="certificate-data">
            @if($certExists && $certData)
                <div class="bg-white rounded shadow mb-6">
                    <div class="p-6">
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
                            @if($isTestCert)
                                <tr>
                                    <td colspan="2">
                                        <div class="alert alert-warning mt-3" role="alert">
                                            To jest certyfikat testowy (staging).
                                        </div>
                                    </td>
                                </tr>
                            @endif
                            @if(auth()->user()->is_root)
                                <tr>
                                    <td colspan="2">
                                        <div class="alert alert-info mt-3" role="alert">
                                            Certyfikat systemowy oraz certyfikat do komunikacji API są ważne.
                                            Tylko użytkownik root widzi pełne dane.
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="p-6 flex gap-3">
                        <button id="download-cert" class="btn btn-success" aria-label="Pobierz certyfikat">
                            <i class="bi bi-download"></i> Pobierz certyfikat
                        </button>
                        <button id="revoke-cert" class="btn btn-danger" aria-label="Cofnij certyfikat">
                            <i class="bi bi-x-circle"></i> Cofnij certyfikat
                        </button>
                    </div>
                </div>
            @else
                <div class="alert alert-warning mb-3" role="alert">
                    Brak certyfikatu. Możesz wygenerować nowy certyfikat.
                </div>
                <button id="generate-cert" class="btn btn-primary" aria-label="Generuj certyfikat">
                    <i class="bi bi-plus-circle"></i> Generuj certyfikat
                </button>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const token = '{{ csrf_token() }}';

            function showAlert(message, type = 'info') {
                const container = document.getElementById('alert-container');
                container.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                                    ${message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                               </div>`;
            }

            // Generowanie certyfikatu
            const generateBtn = document.getElementById('generate-cert');
            if (generateBtn) {
                generateBtn.addEventListener('click', function () {
                    fetch('{{ route("consultations.certificate.generate") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            showAlert(data.message, data.success ? 'success' : 'danger');
                            if(data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Błąd generowania certyfikatu', 'danger'));
                });
            }

            // Cofanie certyfikatu
            const revokeBtn = document.getElementById('revoke-cert');
            if (revokeBtn) {
                revokeBtn.addEventListener('click', function () {
                    if(!confirm('Czy na pewno chcesz cofnąć certyfikat?')) return;
                    fetch('{{ route("consultations.certificate.revoke") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            showAlert(data.message, data.success ? 'success' : 'danger');
                            if(data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Błąd cofania certyfikatu', 'danger'));
                });
            }

            // Pobranie certyfikatu
            const downloadBtn = document.getElementById('download-cert');
            if(downloadBtn) {
                downloadBtn.addEventListener('click', function () {
                    window.location.href = '{{ route("consultations.certificate.download") }}';
                });
            }
        });
    </script>
@endsection
