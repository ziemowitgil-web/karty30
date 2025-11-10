@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 id="certificate-title">Certyfikat użytkownika</h1>

        <div id="alert-container" aria-live="polite"></div>

        <div id="certificate-data">
            @if($certExists && $certData)
                <div class="card mb-3" role="region" aria-label="Dane certyfikatu">
                    <div class="card-header">Dane certyfikatu</div>
                    <div class="card-body">
                        <p><strong>Imię i nazwisko:</strong> {{ $certData['common_name'] }}</p>
                        <p><strong>Email:</strong> {{ $certData['email'] }}</p>
                        <p><strong>Organizacja:</strong> {{ $certData['organization'] }}</p>
                        <p><strong>Jednostka organizacyjna:</strong> {{ $certData['organizational_unit'] }}</p>
                        <p><strong>Ważny od:</strong> {{ $certData['valid_from'] }}</p>
                        <p><strong>Ważny do:</strong> {{ $certData['valid_to'] }}</p>
                        <p><strong>SHA1:</strong> {{ $certData['sha1'] }}</p>
                        @if($isTestCert)
                            <p class="text-warning">To jest certyfikat testowy (staging).</p>
                        @endif
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button id="download-cert" class="btn btn-success" aria-label="Pobierz certyfikat">Pobierz certyfikat</button>
                        <button id="revoke-cert" class="btn btn-danger" aria-label="Cofnij certyfikat">Cofnij certyfikat</button>
                    </div>
                </div>
            @else
                <div class="alert alert-warning mb-3" role="alert">
                    Brak certyfikatu. Możesz wygenerować nowy certyfikat.
                </div>
                <button id="generate-cert" class="btn btn-primary" aria-label="Generuj certyfikat">Generuj certyfikat</button>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const token = '{{ csrf_token() }}';

            // Generowanie certyfikatu
            const generateBtn = document.getElementById('generate-cert');
            if (generateBtn) {
                generateBtn.addEventListener('click', function () {
                    fetch('{{ route("certificate.generate") }}', {
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
                            if (data.success) {
                                setTimeout(() => location.reload(), 500); // odświeżenie, aby pokazać dane certyfikatu
                            }
                        })
                        .catch(err => showAlert('Wystąpił błąd podczas generowania certyfikatu.', 'danger'));
                });
            }

            // Cofanie certyfikatu
            const revokeBtn = document.getElementById('revoke-cert');
            if (revokeBtn) {
                revokeBtn.addEventListener('click', function () {
                    if (!confirm('Czy na pewno chcesz cofnąć certyfikat?')) return;

                    fetch('{{ route("certificate.revoke") }}', {
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
                            if (data.success) {
                                setTimeout(() => location.reload(), 500);
                            }
                        })
                        .catch(err => showAlert('Wystąpił błąd podczas cofania certyfikatu.', 'danger'));
                });
            }

            // Pobranie certyfikatu
            const downloadBtn = document.getElementById('download-cert');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function () {
                    window.location.href = '{{ route("certificate.download") }}';
                });
            }

            function showAlert(message, type = 'info') {
                const container = document.getElementById('alert-container');
                container.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
            }
        });
    </script>
@endsection
