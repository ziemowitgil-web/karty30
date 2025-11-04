@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col bg-gray-100">

        <!-- Główna sekcja logowania -->
        <div class="flex flex-1 items-center justify-center px-4">
            <div class="bg-white shadow-lg rounded-lg w-full max-w-md border border-gray-200">

                <!-- 🔹 Nagłówek -->
                <div class="bg-gray-800 text-white text-center py-5 px-6 rounded-t-lg border-b border-gray-700">
                    <h1 class="text-2xl font-semibold tracking-wide">TyfloKonsultacje</h1>
                    <p class="text-gray-300 text-sm uppercase tracking-wider">System ewidencyjny</p>
                </div>

                <!-- 🔹 Treść logowania -->
                <div class="p-8">

                    <!-- 🔸 Logowanie domenowe -->
                    @if (app()->environment('production'))
                        <button id="domain-login-btn"
                                class="w-full flex items-center justify-center bg-blue-700 text-white font-medium py-3 rounded-md hover:bg-blue-800 transition focus:ring-4 focus:ring-blue-300 focus:outline-none mb-6"
                                aria-label="Logowanie kontem domenowym FEER.ORG.PL">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3zM12 11v10m0 0H6m6 0h6" />
                            </svg>
                            Logowanie domenowe (FEER.ORG.PL)
                        </button>
                    @else
                        <div class="w-full flex flex-col items-center justify-center bg-gray-50 text-gray-500 border border-dashed border-gray-300 py-3 rounded-md mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-2 text-gray-400" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M12 18a9 9 0 100-18 9 9 0 000 18z" />
                            </svg>
                            <span class="text-sm text-center px-4">
                            Logowanie domenowe jest <strong>nieaktywne</strong> w środowisku <span class="font-semibold">{{ strtoupper(env('APP_ENV')) }}</span>.<br>
                            Dostępne wyłącznie w wersji <strong>produkcyjnej</strong>.
                        </span>
                        </div>
                    @endif

                    <!-- Separator -->
                    <div class="flex items-center my-6">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="mx-3 text-gray-400 text-sm bg-white px-2">lub</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>

                    <!-- Formularz logowania -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-gray-700 font-medium mb-1">
                                Adres e-mail
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                   class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                            @error('email') border-red-500 @enderror"
                                   placeholder="np. jan.kowalski@feer.org.pl">
                            @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Hasło -->
                        <div>
                            <label for="password" class="block text-gray-700 font-medium mb-1">
                                Hasło
                            </label>
                            <input id="password" type="password" name="password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                            @error('password') border-red-500 @enderror"
                                   placeholder="Wpisz hasło">
                            @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Zapamiętaj mnie -->
                        <div class="flex items-center">
                            <input id="remember" type="checkbox" name="remember"
                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="remember" class="ml-2 text-gray-700 text-sm">
                                Zapamiętaj mnie
                            </label>
                        </div>

                        <!-- Przycisk logowania -->
                        <button type="submit"
                                class="w-full bg-gray-800 text-white py-3 rounded-md font-semibold hover:bg-gray-900 shadow-sm transition focus:ring-4 focus:ring-gray-400 focus:outline-none">
                            Zaloguj się
                        </button>
                    </form>

                    <!-- Separator -->
                    <div class="flex items-center my-6">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="mx-3 text-gray-400 text-sm bg-white px-2">lub</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>

                    <!-- Logowanie kluczem sprzętowym -->
                    <button id="webauthn-login-btn"
                            class="w-full flex items-center justify-center bg-gray-700 text-white py-3 rounded-md hover:bg-gray-800 shadow-sm transition focus:ring-4 focus:ring-gray-400 focus:outline-none"
                            aria-label="Logowanie kluczem sprzętowym">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3zM12 11v10m0 0H6m6 0h6" />
                        </svg>
                        Logowanie kluczem sprzętowym
                    </button>

                    <!-- Reset hasła -->
                    @if (Route::has('password.request'))
                        <div class="text-center mt-6">
                            <a href="{{ route('password.request') }}" class="text-blue-700 hover:underline text-sm font-medium">
                                Nie pamiętasz hasła?
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 🔹 Stopka -->
        <footer class="text-center text-gray-500 text-xs py-4">

                Wersja systemu:
                <span class="font-medium text-gray-700">{{ env('APP_VERSION', '1.0.0') }}</span>
                &nbsp;•&nbsp;
                Środowisko:
                <span class="font-semibold text-gray-800 uppercase">{{ env('APP_ENV') }}</span>
            </p>
        </footer>
    </div>

    <!-- Skrypt WebAuthn -->
    <script>
        document.getElementById('webauthn-login-btn').addEventListener('click', async () => {
            try {
                const response = await fetch('{{ route('webauthn.challenge') }}', {
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if (!response.ok) throw new Error('Nie udało się pobrać wyzwania WebAuthn.');
                const options = await response.json();
                const credential = await navigator.credentials.get({ publicKey: options });

                const verifyResponse = await fetch('{{ route('webauthn.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(credential)
                });
                if (!verifyResponse.ok) throw new Error('Weryfikacja WebAuthn nie powiodła się.');
                window.location.href = '{{ route('home') }}';
            } catch (err) {
                console.error(err);
                alert(err.message || 'Błąd podczas logowania WebAuthn.');
            }
        });
    </script>
@endsection
