@extends('layouts.app')

@section('content')
    <div class="container my-4">
        <h1>Certyfikat użytkownika</h1>

        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if($certExists && $certData)
            <div class="card mb-3 border-primary" role="region" aria-label="Dane certyfikatu">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span>Dane certyfikatu</span>
                    <div>
                        <a href="{{ route('consultations.certificate.download') }}" class="btn btn-light btn-sm me-2" aria-label="Pobierz certyfikat">
                            Pobierz
                        </a>

                        <form action="{{ route('consultations.certificate.revoke') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" aria-label="Cofnij certyfikat">
                                Cofnij
                            </button>
                        </form>
                    </div>
                </div>
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
            </div>
        @else
            <div class="alert alert-warning mb-3" role="alert">
                Brak certyfikatu. Możesz wygenerować nowy certyfikat.
            </div>

            <form action="{{ route('consultations.certificate.generate') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">Generuj certyfikat</button>
            </form>
        @endif
    </div>
@endsection
