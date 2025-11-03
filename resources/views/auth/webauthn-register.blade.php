@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
        <div class="bg-white shadow-xl rounded-lg w-full max-w-lg p-8">
            <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Zarządzanie kluczami bezpieczeństwa</h2>

            <!-- Lista istniejących kluczy -->
            <div class="mb-6">
                <h3 class="text-gray-700 font-medium mb-2">Zarejestrowane klucze</h3>
                @if($keys->isEmpty())
                    <p class="text-gray-500 text-sm">Brak zarejestrowanych kluczy.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($keys as $key)
                            <li class="flex justify-between items-center p-2 border rounded-md">
                                <span>{{ $key->alias ?? 'Bez nazwy' }}</span>
                                <form method="POST" action="{{ route('webauthn.keys.destroy', $key) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-sm">Usuń</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Rejestracja nowego klucza -->
            <div class="mb-3">
                <button id="register-key" class="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition-colors">
                    Dodaj nowy klucz sprzętowy
                </button>
            </div>

            <div id="message" class="text-center mt-4 text-red-500"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('register-key').addEventListener('click', async () => {
                const messageEl = document.getElementById('message');
                messageEl.textContent = '';

                try {
                    // Pobierz challenge z serwera
                    const optionsResp = await fetch("{{ route('webauthn.keys.options') }}", {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const options = await optionsResp.json();

                    // Konwersja challenge i ID użytkownika na Uint8Array
                    options.challenge = Uint8Array.from(atob(options.challenge), c => c.charCodeAt(0));
                    options.user.id = Uint8Array.from(atob(options.user.id), c => c.charCodeAt(0));

                    if (options.excludeCredentials) {
                        options.excludeCredentials = options.excludeCredentials.map(cred => ({
                            ...cred,
                            id: Uint8Array.from(atob(cred.id), c => c.charCodeAt(0))
                        }));
                    }

                    // Tworzenie nowego klucza
                    const credential = await navigator.credentials.create({ publicKey: options });

                    // Przygotowanie odpowiedzi
                    const credentialResponse = {
                        id: credential.id,
                        rawId: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
                        type: credential.type,
                        response: {
                            attestationObject: btoa(String.fromCharCode(...new Uint8Array(credential.response.attestationObject))),
                            clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON)))
                        }
                    };

                    // Wyślij odpowiedź do serwera
                    const verifyResp = await fetch("{{ route('webauthn.keys.register') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(credentialResponse)
                    });

                    if (!verifyResp.ok) throw new Error('Rejestracja klucza nie powiodła się');

                    window.location.reload();

                } catch (err) {
                    console.error(err);
                    messageEl.textContent = 'Błąd: ' + err.message;
                }
            });
        </script>
    @endpush
@endsection
