@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Panel administratora</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Użytkownicy -->
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h2 class="text-xl font-semibold mb-2">Użytkownicy</h2>
                <p class="text-4xl font-bold text-gray-800 mb-4">{{ $userCount }}</p>
                <a href="{{ route('admin.users.list') }}" class="text-blue-500 hover:underline">Zarządzaj użytkownikami</a>
            </div>

            <!-- Logi aktywności -->
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h2 class="text-xl font-semibold mb-2">Logi aktywności</h2>
                <p class="text-4xl font-bold text-gray-800 mb-4">{{ $logCount }}</p>
                <a href="{{ route('admin.logs') }}" class="text-blue-500 hover:underline">Przeglądaj wszystkie logi</a>
            </div>

            <!-- Informacje o serwerze -->
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h2 class="text-xl font-semibold mb-2">Informacje o serwerze</h2>
                <p class="mt-2"><strong>PHP:</strong> {{ phpversion() }}</p>
                <p class="mt-2"><strong>System:</strong> {{ php_uname() }}</p>
                <p class="mt-2">
                    <strong>Certyfikat serwera:</strong>
                    @if(file_exists(storage_path('certificates/server.crt')))
                        <span class="text-green-600 font-semibold">Wygenerowany</span>
                    @else
                        <span class="text-red-600 font-semibold">Nie istnieje</span>
                    @endif
                </p>
                <a href="{{ route('admin.certificate.form') }}" class="text-blue-500 hover:underline mt-2 inline-block">Zarządzaj certyfikatem</a>
            </div>
        </div>

        <!-- Ostatnie logi -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-semibold mb-4">Ostatnie logi aktywności</h2>
            @if($recentLogs->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                        <tr>
                            <th class="border-b p-3 text-gray-700">Użytkownik</th>
                            <th class="border-b p-3 text-gray-700">Akcja</th>
                            <th class="border-b p-3 text-gray-700">Data</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($recentLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="border-b p-3">{{ $log->causer?->name ?? 'System' }}</td>
                                <td class="border-b p-3">{{ $log->description }}</td>
                                <td class="border-b p-3">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500">Brak logów do wyświetlenia.</p>
            @endif
        </div>
    </div>
@endsection
