@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto p-6 space-y-6">

        {{-- Nagłówek --}}
        <h1 class="text-2xl font-bold mb-6">Twoje certyfikaty</h1>

        {{-- Wyjaśnienie celu strony --}}
        <div class="p-4 rounded border border-gray-300 bg-gray-50">
            <h2 class="text-lg font-semibold mb-2">Co to jest certyfikat X.509?</h2>
            <p>
                Certyfikat X.509 to cyfrowy dokument potwierdzający Twoją tożsamość w systemie
                i umożliwiający <strong>podpis elektroniczny dokumentów</strong>.
            </p>
            <ul class="list-disc ml-5 mt-2 text-gray-700">
                <li>wygenerowanie nowego certyfikatu, jeśli go jeszcze nie masz,</li>
                <li>pobranie istniejącego certyfikatu,</li>
                <li>cofnięcie certyfikatu, jeśli nie jest już potrzebny.</li>
            </ul>
            <p class="mt-2 text-sm text-red-600">
                ⚠ Jeżeli nie wiesz co robisz – <strong>nie ruszaj żadnych opcji!</strong>
            </p>
        </div>

        {{-- Ścieżka certyfikacji --}}
        <div class="mb-6">
            <h2 class="font-semibold mb-2">Ścieżka certyfikacji</h2>
            <div class="flex items-center justify-between">
                @php
                    $steps = [
                        ['label' => 'Brak certyfikatu', 'status' => !$certExists ? 'current' : 'done'],
                        ['label' => 'Certyfikat testowy', 'status' => $isTestCert ? 'current' : ($certExists && !$isTestCert ? 'done' : 'pending')],
                        ['label' => 'Certyfikat produkcyjny', 'status' => $certExists && !$isTestCert ? 'current' : 'pending'],
                    ];
                @endphp

                @foreach($steps as $i => $step)
                    <div class="flex-1 flex items-center">
                        <div class="flex flex-col items-center">
                            <div class="w-6 h-6 rounded-full mb-1
                            @if($step['status'] === 'done') bg-green-500
                            @elseif($step['status'] === 'current') bg-yellow-400
                            @else bg-gray-300
                            @endif
                            flex items-center justify-center text-white font-bold
                        ">
                                {{ $i + 1 }}
                            </div>
                            <span class="text-xs text-center">{{ $step['label'] }}</span>
                        </div>
                        @if(!$loop->last)
                            <div class="flex-1 h-1
                            @if($step['status'] === 'done') bg-green-500
                            @elseif($step['status'] === 'current') bg-yellow-300
                            @else bg-gray-200
                            @endif
                            mx-2 rounded"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Status certyfikatu --}}
        <div class="p-4 rounded border
        @if(!$certExists) border-gray-300 bg-gray-50
        @elseif($isTestCert) border-yellow-400 bg-yellow-50
        @else border-green-400 bg-green-50
        @endif
        flex items-center">
        <span class="w-4 h-4 rounded-full mr-2
            @if(!$certExists) bg-gray-400
            @elseif($isTestCert) bg-yellow-400
            @else bg-green-500
            @endif
        "></span>
            <div>
                @if(!$certExists)
                    <span class="font-semibold">Nie posiadasz jeszcze certyfikatu.</span>
                @elseif($isTestCert)
                    <span class="font-semibold">Certyfikat testowy</span> – działa tylko w środowisku testowym.
                @else
                    <span class="font-semibold">Certyfikat produkcyjny aktywny</span>
                @endif
            </div>
        </div>

        {{-- Generowanie certyfikatu --}}
        @if(!$certExists)
            <div class="p-4 rounded border border-blue-400 bg-blue-50">
                <h2 class="font-semibold mb-2">Generowanie nowego certyfikatu</h2>
                <input type="password" id="certPassword" placeholder="Hasło (min. 6 znaków)"
                       class="border p-2 rounded w-full mb-2">
                <button id="generateCert" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Generuj certyfikat
                </button>
                <div id="certMessage" class="mt-2 text-sm text-gray-700"></div>
            </div>
        @endif

        {{-- Akcje dla istniejącego certyfikatu --}}
        @if($certExists)
            <div class="p-4 rounded border border-gray-300 bg-gray-50 space-y-2">
                <h2 class="font-semibold mb-2">Akcje certyfikatu</h2>

                <div class="flex gap-2">
                    <a href="{{ route('consultations.certificate.download') }}"
                       class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Pobierz certyfikat
                    </a>

                    <button id="revokeCert"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Cofnij certyfikat
                    </button>
                </div>

                <p class="text-sm text-gray-600 mt-2">
                    Pobranie certyfikatu pozwala Ci go bezpiecznie przechowywać.
                    Cofnięcie certyfikatu usuwa go z systemu i nie będzie możliwe użycie go do podpisu.
                </p>
            </div>
        @endif

        {{-- Szczegóły certyfikatu --}}
        @if($certExists && $certData)
            <div class="p-4 rounded border border-gray-300 bg-gray-50">
                <h2 class="font-semibold mb-2">Szczegóły certyfikatu</h2>
                <table class="table-auto w-full text-sm text-gray-700">
                    <tbody>
                    <tr><td class="font-semibold pr-4">Nazwa posiadacza:</td><td>{{ $certData['common_name'] }}</td></tr>
                    <tr><td class="font-semibold pr-4">Email:</td><td>{{ $certData['email'] }}</td></tr>
                    <tr><td class="font-semibold pr-4">Organizacja:</td><td>{{ $certData['organization'] }}</td></tr>
                    <tr><td class="font-semibold pr-4">Jednostka organizacyjna:</td><td>{{ $certData['organizational_unit'] }}</td></tr>
                    <tr><td class="font-semibold pr-4">Ważny od:</td><td>{{ $certData['valid_from'] }}</td></tr>
                    <tr><td class="font-semibold pr-4">Ważny do:</td><td>{{ $certData['valid_to'] }}</td></tr>
                    <tr><td class="font-semibold pr-4">SHA1:</td><td>{{ $certData['sha1'] }}</td></tr>
                    </tbody>
                </table>
            </div>
        @endif

    </div>

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                @if(!$certExists)
                // Generowanie certyfikatu
                document.getElementById('generateCert').addEventListener('click', async function() {
                    const password = document.getElementById('certPassword').value;
                    const messageDiv = document.getElementById('certMessage');
                    messageDiv.textContent = '';

                    if(password.length < 6) {
                        messageDiv.textContent = 'Hasło musi mieć minimum 6 znaków.';
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
                        if(data.success) {
                            messageDiv.textContent = 'Certyfikat został wygenerowany!';
                            setTimeout(()=>{ location.reload(); }, 1000);
                        } else {
                            messageDiv.textContent = 'Błąd: ' + data.message;
                        }

                    } catch(err) {
                        messageDiv.textContent = 'Błąd serwera. Spróbuj później.';
                        console.error(err);
                    }
                });
                @endif

                @if($certExists)
                // Cofanie certyfikatu
                document.getElementById('revokeCert').addEventListener('click', async function() {
                    if(!confirm('Na pewno chcesz cofnąć certyfikat? Operacja jest nieodwracalna!')) return;
                    try {
                        const response = await fetch("{{ route('consultations.certificate.revoke') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await response.json();
                        alert(data.message);
                        if(data.success) location.reload();
                    } catch(err) {
                        alert('Błąd serwera.');
                        console.error(err);
                    }
                });
                @endif

            });
        </script>
    @endsection

@endsection
