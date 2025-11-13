@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
        <div class="max-w-md w-full bg-white shadow-lg rounded-lg border border-gray-200 overflow-hidden">

            <!-- Nagłówek -->
            <div class="bg-gray-800 text-white text-center py-6 px-6 border-b border-gray-700">
                <h2 class="text-2xl font-semibold">Resetowanie / Nadanie hasła</h2>
            </div>

            <div class="p-8 space-y-6">

                <!-- Status -->
                @if (session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Formularz -->
                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-gray-700 font-medium mb-1">Adres e-mail</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="np. jan.kowalski@feer.org.pl">
                        @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Przycisk -->
                    <div>
                        <button type="submit"
                                class="w-full bg-blue-700 text-white py-3 rounded-md font-semibold hover:bg-blue-800 transition focus:ring-4 focus:ring-blue-300 focus:outline-none">
                            Wyślij link do ustawienia hasła
                        </button>
                    </div>
                </form>

                <!-- Informacja dodatkowa -->
                <p class="text-gray-500 text-sm text-center mt-4">
                    Pierwsze logowanie? <a href="{{ route('password.request') }}" class="text-blue-700 hover:underline font-medium">Nadaj hasło i aktywuj konto</a>
                </p>
            </div>
        </div>
    </div>
@endsection
