@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-3xl">

        <h1 class="text-3xl font-bold mb-6 text-gray-900">Certyfikat użytkownika</h1>

        <div id="alert-container" aria-live="polite" class="mb-4"></div>

        @if($certExists && $certData)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 space-y-4">

                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Dane certyfikatu</h2>
                        @if($isTestCert)
                            <span class="px-3 py-1 bg-yellow-200 text-yellow-800 rounded-full text-sm font-medium">
                            Testowy
                        </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600 font-medium">Imię i nazwisko</p>
                            <p class="text-gray-800">{{ $certData['common_name'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Email</p>
                            <p class="text-gray-800">{{ $certData['email'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Organizacja</p>
                            <p class="text-gray-800">{{ $certData['organization'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Jednostka organizacyjna</p>
                            <p class="text-gray-800">{{ $certData['organizational_unit'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Ważny od</p>
                            <p class="text-gray-800">{{ $certData['valid_from'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Ważny do</p>
                            <p class="text-gray-800">{{ $certData['valid_to'] }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-gray-600 font-medium">SHA1</p>
                            <p class="text-gray-800 font-mono break-all">{{ $certData['sha1'] }}</p>
                        </div>
                    </div>

                    @if(auth()->user()->is_root)
                        <div class="p-3 bg-blue-50 text-blue-800 rounded-md text-sm">
                            Certyfikat systemowy oraz certyfikat do komunikacji API są ważne.
                            Tylko użytkownik root widzi pełne dane.
                        </div>
                    @endif

                </div>

                <div class="p-6 flex flex-col sm:flex-row gap-3 border-t border-gray-100 bg-gray-50">
                    <button id="download-cert" class="btn btn-success flex-1 flex justify-center items-center gap-2" aria-label="Pobierz certyfikat">
                        <i class="bi bi-download"></i> Pobierz certyfikat
                    </button>
                    <button id="revoke-cert" class="btn btn-danger flex-1 flex justify-center items-center gap-2" aria-label="Cofnij certyfikat">
                        <i class="bi bi-x-circle"></i> Cofnij certyfikat
                    </button>
                </div>
            </div>

        @else
            <div class="bg-white p-6 rounded-xl shadow flex flex-col gap-4 items-center">
                <div class="text-gray-700 text-center">
                    Brak certyfikatu. Możesz wygenerować nowy certyfikat.
                </div>
                <button id="generate-cert" class="btn btn-primary flex items-center gap-2" aria-label="Generuj certyfikat">
                    <i class="bi bi-plus-circle"></i> Generuj certyfikat
                </button>
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
                container.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                                    ${message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                               </div>`;
            }

            const generateBtn = document.getElementById('generate-cert');
            if (generateBtn) {
                generateBtn.addEventListener('click', function () {
                    fetch('{{ route("consultations.certificate.generate") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            showAlert(data.message, data.success ? 'success' : 'danger');
                            if(data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Błąd generowania certyfikatu', 'danger'));
                });
            }

            const revokeBtn = document.getElementById('revoke-cert');
            if (revokeBtn) {
                revokeBtn.addEventListener('click', function () {
                    if(!confirm('Czy na pewno chcesz cofnąć certyfikat?')) return;
                    fetch('{{ route("consultations.certificate.revoke") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            showAlert(data.message, data.success ? 'success' : 'danger');
                            if(data.success) setTimeout(() => location.reload(), 500);
                        })
                        .catch(() => showAlert('Błąd cofania certyfikatu', 'danger'));
                });
            }

            const downloadBtn = document.getElementById('download-cert');
            if(downloadBtn) {
                downloadBtn.addEventListener('click', function () {
                    window.location.href = '{{ route("consultations.certificate.download") }}';
                });
            }
        });
    </script>
@endsection
