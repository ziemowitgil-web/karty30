@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Certyfikat użytkownika</h1>

        @if($certExists && $certData)
            <div class="card mb-3">
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
            </div>
        @else
            <div class="alert alert-warning">
                Brak certyfikatu. Możesz wygenerować nowy certyfikat.
            </div>

            <form action="{{ route('certificate.generate') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Generuj certyfikat</button>
            </form>
        @endif
    </div>
@endsection
