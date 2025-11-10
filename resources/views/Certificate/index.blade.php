@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h1 id="certificate-title" class="mb-4">Certyfikat użytkownika</h1>

        <div id="alert-container" aria-live="polite"></div>

        <div id="certificate-data">
            @if($certExists && $certData)
                <div class="card shadow-sm mb-4" role="region" aria-label="Dane certyfikatu">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h5 mb-0">Dane certyfikatu</h2>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Imię i nazwisko:</dt>
                            <dd class="col-sm-8">{{ $certData['common_name'] }}</dd>

                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">{{ $certData['email'] }}</dd>

                            <dt class="col-sm-4">Organizacja:</dt>
                            <dd class="col-sm-8">{{ $certData['organization'] }}</dd>

                            <dt class="col-sm-4">Jednostka organizacyjna:</dt>
                            <dd class="col-sm-8">{{ $certData['organizational_unit'] }}</dd>

                            <dt class="col-sm-4">Ważny od:</dt>
                            <dd class="col-sm-8">{{ $certData['valid_from'] }}</dd>

                            <dt class="col-sm-4">Ważny do:</dt>
                            <dd class="col-sm-8">{{ $certData['valid_to'] }}</dd>

                            <dt class="col-sm-4">SHA1:</dt>
                            <dd class="col-sm-8 text-monospace">{{ $certData['sha1'] }}</dd>
                        </dl>

                        @if($isTestCert)
                            <div class="alert alert-warning mt-3" role="alert">
                                To jest certyfikat testowy (staging).
                            </div>
                        @endif
                    </div>
                    <div class="card-footer d-flex gap-2 justify-content-start">
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
                            if (data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Wystąpił błąd podczas generowania certyfikatu.', 'danger'));
                });
            }

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
                        .catch(() => showAlert('Wystąpił błąd podczas cofania certyfikatu.', 'danger'));
                });
            }

            const downloadBtn = document.getElementById('download-cert');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function () {
                    window.location.href = '{{ route("consultations.certificate.download") }}';
                });
            }

            function showAlert(message, type = 'info') {
                const container = document.getElementById('alert-container');
                container.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                                    ${message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                               </div>`;
            }
        });
    </script>
@endsection
