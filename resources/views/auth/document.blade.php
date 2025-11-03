{{-- resources/views/auth/document.blade.php --}}

@extends('layouts.app')

@section('title', 'Uzupełnij dane dokumentu')

@section('content')
    <div class="max-w-lg mx-auto mt-10 bg-white shadow-md rounded-xl p-6">
        <h2 class="text-2xl font-semibold mb-6 text-center text-gray-700">Dane dokumentu uprawniającego</h2>

        {{-- Komunikaty sukcesu lub błędu --}}
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error') || session('warning'))
            <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg">
                {{ session('error') ?? session('warning') }}
            </div>
        @endif

        <form method="POST" action="{{ route('user.document.store') }}">
            @csrf

            {{-- Typ dokumentu --}}
            <div class="mb-4">
                <label for="document_type" class="block text-gray-700 font-medium mb-1">Typ dokumentu</label>
                <input type="text" name="document_type" id="document_type"
                       value="{{ old('document_type', $user->document_type) }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('document_type')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Numer dokumentu --}}
            <div class="mb-4">
                <label for="document_number" class="block text-gray-700 font-medium mb-1">Numer dokumentu</label>
                <input type="text" name="document_number" id="document_number"
                       value="{{ old('document_number', $user->document_number) }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('document_number')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Wystawca dokumentu --}}
            <div class="mb-6">
                <label for="document_issuer" class="block text-gray-700 font-medium mb-1">Wystawca dokumentu</label>
                <input type="text" name="document_issuer" id="document_issuer"
                       value="{{ old('document_issuer', $user->document_issuer) }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('document_issuer')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Przycisk --}}
            <div class="flex justify-center">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg shadow">
                    Zapisz dane dokumentu
                </button>
            </div>
        </form>
    </div>
@endsection
