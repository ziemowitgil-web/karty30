@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow p-6 space-y-6">

        <h1 class="text-xl font-semibold text-gray-900">Szczegóły certyfikatu</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><strong>CN:</strong> {{ $certInfo['subject']['CN'] ?? 'Nieznany' }}</p>
                <p><strong>O:</strong> {{ $certInfo['subject']['O'] ?? '-' }}</p>
                <p><strong>Email:</strong> {{ $certInfo['subject']['emailAddress'] ?? '-' }}</p>
                <p><strong>Ważny od:</strong> {{ isset($certInfo['validFrom_time_t']) ? date('d.m.Y', $certInfo['validFrom_time_t']) : '-' }}</p>
                <p><strong>Ważny do:</strong> {{ isset($certInfo['validTo_time_t']) ? date('d.m.Y', $certInfo['validTo_time_t']) : '-' }}</p>
            </div>
            <div class="flex flex-col gap-2">
                <a href="{{ route('certificates.download', ['userId' => Auth::user()->id, 'type' => 'cert']) }}"
                   class="px-3 py-1 bg-green-100 text-green-800 rounded-xl hover:bg-green-200 transition">Pobierz certyfikat</a>
                <a href="{{ route('certificates.download', ['userId' => Auth::user()->id, 'type' => 'key']) }}"
                   class="px-3 py-1 bg-teal-100 text-teal-800 rounded-xl hover:bg-teal-200 transition">Pobierz klucz</a>
                <form method="POST" action="{{ route('certificates.revoke', ['userId' => Auth::user()->id]) }}">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1 bg-red-100 text-red-800 rounded-xl hover:bg-red-200 transition"
                            onclick="return confirm('Czy na pewno chcesz cofnąć certyfikat?')">Cofnij certyfikat</button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto bg-gray-50 p-3 rounded-xl">
            <h2 class="font-semibold text-gray-800 mb-2">PEM certyfikatu</h2>
            <pre class="text-xs text-gray-700">{{ File::get($certPath) }}</pre>
        </div>

    </div>
@endsection
