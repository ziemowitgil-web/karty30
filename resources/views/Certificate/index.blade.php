@extends('layouts.app')

@section('content')
    <div class="container my-4">
        <h1 class="mb-4">Certyfikat użytkownika</h1>

        @if($certExists && $certData)
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span>Dane certyfikatu</span>
                    @if($isTestCert)
                        <span class="badge bg-warning text-dark">Testowy (staging)</span>
                    @endif
                </div>
                <div class="card-body">
                    <ul class="timeline list-unstyled">
                        <li class="mb-3">
                            <i class="bi bi-person-fill text-primary me-2"></i>
                            <strong>Imię i nazwisko:</strong>
                            <span data-bs-toggle="tooltip" title="Imię i nazwisko z certyfikatu">{{ $certData['common_name'] }}</span>
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-envelope-fill text-success me-2"></i>
                            <strong>Email:</strong>
                            <span data-bs-toggle="tooltip" title="Adres email powiązany z certyfikatem">{{ $certData['email'] }}</span>
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-building text-info me-2"></i>
                            <strong>Organizacja:</strong>
                            <span data-bs-toggle="tooltip" title="Organizacja certyfikatu">{{ $certData['organization'] }}</span>
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-diagram-3-fill text-secondary me-2"></i>
                            <strong>Jednostka organizacyjna:</strong>
                            <span data-bs-toggle="tooltip" title="Jednostka organizacyjna">{{ $certData['organizational_unit'] }}</span>
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-calendar-event-fill text-warning me-2"></i>
                            <strong>Ważny od:</strong> {{ $certData['valid_from'] }}
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-calendar-event-fill text-danger me-2"></i>
                            <strong>Ważny do:</strong> {{ $certData['valid_to'] }}
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-shield-lock-fill text-dark me-2"></i>
                            <strong>SHA1:</strong> <span data-bs-toggle="tooltip" title="Fingerprint certyfikatu">{{ $certData['sha1'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        @else
            <div class="alert alert-warning mb-4 d-flex justify-content-between align-items-center">
                <span>Brak certyfikatu. Możesz wygenerować nowy certyfikat.</span>
            </div>

            <form action="{{ route('consultations.certificate.generate') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-key-fill me-2"></i> Generuj certyfikat
                </button>
            </form>
        @endif
    </div>

    <!-- Inicjalizacja tooltipów Bootstrap 5 -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>

    <style>
        /* Timeline prosty styl */
        .timeline li {
            position: relative;
            padding-left: 30px;
        }
        .timeline li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 6px;
            width: 10px;
            height: 10px;
            background-color: #0d6efd;
            border-radius: 50%;
        }
    </style>
@endsection
