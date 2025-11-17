@extends('layouts.app')

@section('content')
    <div class="max-w-lg mx-auto bg-white rounded-2xl shadow p-6">

        <h1 class="text-xl font-semibold text-gray-900 mb-4">Generowanie certyfikatu</h1>

        <form action="{{ route('certificates.generate.post', ['userId' => Auth::user()->id]) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="key_password" class="block text-gray-700 font-medium mb-2">Hasło do klucza prywatnego</label>
                <input type="password" name="key_password" id="key_password"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required minlength="6">
                @error('key_password')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                Wygeneruj certyfikat
            </button>
        </form>
    </div>
@endsection
