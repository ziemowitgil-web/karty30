@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-4xl">

        <h1 class="text-3xl font-bold mb-6 text-gray-900">Twój certyfikat do podpisu dokumentacji</h1>

        <div id="alert-container" aria-live="polite" class="mb-4"></div>

        @if($certExists && $certData)
            <div class="bg-white rounded shadow p-6">

                <h2 class="text-2xl font-semibold mb-4">Dane certyfikatu</h2>
                <table class="w-full table-auto border-collapse mb-6">
                    <tbody>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Imię i nazwisko</td>
                        <td class="py-2 px-4 border-b">{{ $certData['common_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Email</td>
                        <td class="py-2 px-4 border-b">{{ $certData['email'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Organizacja</td>
                        <td class="py-2 px-4 border-b">{{ $certData['organization'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Jednostka</td>
                        <td class="py-2 px-4 border-b">{{ $certData['organizational_unit'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Autoryzował</td>
                        <td class="py-2 px-4 border-b">Ziemowit Gil (FEER)</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Ścieżka certyfikacji</td>
                        <td class="py-2 px-4 border-b font-mono break-all">Krajowa Izba Rozliczeniowa → UMWM → FEER → {{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Ważny od</td>
                        <td class="py-2 px-4 border-b">{{ $certData['valid_from'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">Ważny do</td>
                        <td class="py-2 px-4 border-b">{{ $certData['valid_to'] }}</td>
                    </tr>
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">SHA1</td>
                        <td class="py-2 px-4 border-b font-mono break-all">{{ $certData['sha1'] }}</td>
                    </tr>
                    </tbody>
                </table>

                <div class="flex flex-col md:flex-row gap-3">
                    <button id="download-cert" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:ring-2 focus:ring-green-300 focus:outline-none">Pobierz certyfikat</button>
                    <button id="revoke-cert" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none">Cofnij certyfikat</button>
                </div>

                <p class="mt-6 text-gray-600">Twój certyfikat służy do podpisu dokumentacji w systemie. Wszystkie dane w certyfikacie są widoczne tylko dla systemu.</p>
            </div>

        @else
            <div class="bg-yellow-100 p-4 rounded shadow mb-4">
                Nie posiadasz jeszcze certyfikatu. Możesz wygenerować nowy certyfikat do podpisu dokumentacji.
            </div>

            <div class="bg-white rounded shadow p-6">
                <h2 class="text-xl font-semibold mb-2">Generowanie certyfikatu</h2>
                <p class="mb-4 text-gray-700">
                    Hasło chroni Twój klucz prywatny. Wybierz silne hasło, które będziesz pamiętać. Będzie potrzebne do używania certyfikatu w systemie.
                </p>
                <div class="flex flex-col md:flex-row gap-3 items-start">
                    <input type="password" id="cert-password" placeholder="Hasło do certyfikatu" class="px-4 py-2 border rounded focus:ring-2 focus:ring-blue-300 focus:outline-none w-full md:w-1/3">
                    <button id="generate-cert" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none">Generuj certyfikat</button>
                </div>

                <h3 class="mt-6 font-semibold">Dane, które znajdą się w certyfikacie:</h3>
                <ul class="list-disc list-inside text-gray-700">
                    <li>Imię i nazwisko: {{ $user->name }}</li>
                    <li>Email: {{ $user->email }}</li>
                    <li>Organizacja: FEER</li>
                    <li>Jednostka: Certyfikaty podpisu dokumentacji</li>
                    <li>Autoryzował: Ziemowit Gil (FEER)</li>
                    <li>Ścieżka certyfikacji: Krajowa Izba Rozliczeniowa → UMWM → FEER → {{ $user->name }}</li>
                </ul>
            </div>
        @endif

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const token = '{{ csrf_token() }}';

            function showAlert(message, type = 'info') {
                const container = document.getElementById('alert-container');
                container.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
            }

            const generateBtn = document.getElementById('generate-cert');
            if(generateBtn){
                generateBtn.addEventListener('click', function(){
                    const password = document.getElementById('cert-password').value.trim();
                    if(!password){ showAlert('Podaj hasło do certyfikatu', 'warning'); return; }

                    fetch('{{ route("consultations.certificate.generate") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ password })
                    })
                        .then(res => res.json())
                        .then(data => {
                            showAlert(data.message, data.success ? 'success' : 'danger');
                            if(data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Błąd podczas generowania certyfikatu', 'danger'));
                });
            }

            const downloadBtn = document.getElementById('download-cert');
            if(downloadBtn){
                downloadBtn.addEventListener('click', function(){
                    window.location.href = '{{ route("consultations.certificate.download") }}';
                });
            }

            const revokeBtn = document.getElementById('revoke-cert');
            if(revokeBtn){
                revokeBtn.addEventListener('click', function(){
                    if(!confirm('Czy na pewno chcesz cofnąć certyfikat?')) return;

                    fetch('{{ route("consultations.certificate.revoke") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            showAlert(data.message, data.success ? 'success' : 'danger');
                            if(data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(()=>showAlert('Błąd podczas cofania certyfikatu','danger'));
                });
            }
        });
    </script>
@endsection
