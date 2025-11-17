@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        <h1 class="text-2xl font-bold text-gray-900">Zarządzanie certyfikatem</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('success') }}</div>
        @endif

        @php
            $certExists = $certExists ?? false;
            $certCN = $certExists ? ($certData['subject']['CN'] ?? 'Nieznany') : 'Brak certyfikatu';
            $certOrg = $certExists ? ($certData['subject']['O'] ?? '-') : '-';
            $certValidUntil = $certExists ? date('d.m.Y', $certData['validTo_time_t']) : '-';
            $certStatus = $certExists ? ((time() < $certData['validTo_time_t']) ? 'Aktywny' : 'Wygasł') : 'Brak';
        @endphp

        <section class="bg-white p-6 rounded-2xl shadow flex justify-between items-center">
            <div>
                <p class="font-semibold">Certyfikat: {{ $certCN }}</p>
                <p class="text-sm text-gray-600">Organizacja: {{ $certOrg }}</p>
                <p class="text-sm text-gray-600">Status: {{ $certStatus }} @if($certStatus === 'Aktywny') (do {{ $certValidUntil }}) @endif</p>
            </div>

            <div class="flex gap-2">
                @if($certExists)
                    <form method="POST" action="{{ route('certificates.revoke', Auth::id()) }}">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Cofnij certyfikat</button>
                    </form>
                @else
                    <a href="{{ route('certificates.generate') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Wygeneruj certyfikat</a>
                @endif
            </div>
        </section>

    </div>
@endsection
