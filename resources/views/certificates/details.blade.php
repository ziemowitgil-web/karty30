@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-xl">
        <h1 class="text-2xl font-bold mb-4">Szczegóły certyfikatu</h1>

        <div class="mb-4">
            <p><strong>Certyfikat:</strong> {{ basename($certPath) }}</p>
            <p><strong>Klucz prywatny:</strong> {{ basename($keyPath) }}</p>
        </div>

        <div class="flex gap-4">
            <a href="{{ route('certificates.download', ['userId' => auth()->id(), 'type' => 'cert']) }}"
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Pobierz certyfikat
            </a>

            <a href="{{ route('certificates.download', ['userId' => auth()->id(), 'type' => 'key']) }}"
               class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                Pobierz klucz prywatny
            </a>
        </div>
    </div>
@endsection
