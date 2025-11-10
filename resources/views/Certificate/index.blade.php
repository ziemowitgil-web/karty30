{{-- resources/views/Certificate/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-2xl font-bold mb-4">Zarządzanie certyfikatem</h1>

        {{-- Status certyfikatu --}}
        @if($certExists)
            <div class="mb-4 p-4 rounded border border-green-400 bg-green-50">
                <h2 class="font-semibold">Certyfikat aktywny {{ $isTestCert ? '(TESTOWY)' : '' }}</h2>
                <ul class="mt-2">
                    <li><strong>Imię i nazwisko:</strong> {{ $certData['common_name'] }}</li>
                    <li><strong>Email:</strong> {{ $certData['email'] }}</li>
                    <li><strong>Organizacja:</strong> {{ $certData['organization'] }}</li>
                    <li><strong>Jednostka organizacyjna:</strong> {{ $certData['organizational_unit'] ?? '-' }}</li>
                    <li><strong>SHA1:</strong> {{ $certData['sha1'] }}</li>
                    <li><strong>Ważny od:</strong> {{ $certData['valid_from'] }}</li>
                    <li><strong>Ważny do:</strong> {{ $certData['valid_to'] }}</li>
                </ul>
            </div>
        @else
            <div class="mb-4 p-4 rounded border border-gray-400 bg-gray-50">
                <p>Nie masz jeszcze certyfikatu X.509. Możesz go wygenerować poniżej.</p>
            </div>
        @endif

        {{-- Formularz generowania certyfikatu --}}
        <div class="mb-4 p-4 rounded border border-blue-400 bg-blue-50">
            <h2 class="font-semibold mb-2">Generowanie nowego certyfikatu</h2>
            <input type="password" id="certPassword" placeholder="Hasło (min. 6 znaków)"
                   class="border p-2 rounded w-full mb-2">
            <button id="generateCert" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Generuj certyfikat</button>
            <div id="certMessage" class="mt-2 text-sm"></div>
        </div>

        {{-- Akcje certyfikatu --}}
        @if($certExists)
            <div class="mb-4 p-4 rounded border border-gray-300 bg-gray-50 flex gap-2 flex-wrap">
                <a href="{{ route('consultations.certificate.download') }}"
                   class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Pobierz certyfikat</a>
                <button id="revokeCert" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Cofnij certyfikat</button>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const msgDiv = document.getElementById('certMessage');

            // Generowanie certyfikatu
            document.getElementById('generateCert').addEventListener('click', async () => {
                const password = document.getElementById('certPassword').value;
                msgDiv.textContent = '';

                if(password.length < 6){
                    msgDiv.textContent = 'Hasło musi mieć min. 6 znaków ❌';
                    return;
                }

                try {
                    const response = await fetch("{{ route('consultations.certificate.generate') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ password })
                    });

                    const data = await response.json();

                    if(response.ok){
                        msgDiv.textContent = data.message + ' ✅';
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        msgDiv.textContent = data.message || 'Błąd serwera ❌';
                        console.error(data);
                    }
                } catch(err){
                    msgDiv.textContent = 'Błąd połączenia ❌';
                    console.error(err);
                }
            });

            // Cofanie certyfikatu
            const revokeBtn = document.getElementById('revokeCert');
            if(revokeBtn){
                revokeBtn.addEventListener('click', async () => {
                    if(!confirm('Czy na pewno chcesz cofnąć certyfikat?')) return;

                    try {
                        const response = await fetch("{{ route('consultations.certificate.revoke') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();
                        if(response.ok && data.success){
                            msgDiv.textContent = data.message + ' ✅';
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            msgDiv.textContent = data.message || 'Błąd przy cofaniu certyfikatu ❌';
                        }
                    } catch(err){
                        msgDiv.textContent = 'Błąd połączenia ❌';
                        console.error(err);
                    }
                });
            }
        });
    </script>
@endsection
