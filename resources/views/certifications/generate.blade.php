@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-xl">
        <h1 class="text-2xl font-bold mb-4">Generowanie certyfikatu X.509</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 mb-4 rounded">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('certificates.generate.post') }}">
            @csrf

            <div class="mb-4">
                <label for="key_password" class="block font-medium mb-1">
                    Hasło do certyfikatu (inne niż hasło konta)
                </label>
                <input type="password" name="key_password" id="key_password"
                       class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-500"
                       required minlength="6">
                @error('key_password')
                <p class="text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Generuj certyfikat
            </button>
        </form>
    </div>
@endsection
