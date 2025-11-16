@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Panel administratora</h1>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <!-- Użytkownicy -->
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-semibold">Użytkownicy</h2>
                <p class="text-3xl mt-2">{{ $userCount }}</p>
                <a href="{{ route('admin.users.list') }}" class="text-blue-500 mt-2 inline-block">Zarządzaj użytkownikami</a>
            </div>

            <!-- Logi aktywności -->
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-semibold">Logi aktywności</h2>
                <p class="text-3xl mt-2">{{ $logCount }}</p>
                <a href="{{ route('admin.logs') }}" class="text-blue-500 mt-2 inline-block">Przeglądaj wszystkie logi</a>
            </div>

            <!-- Informacje o serwerze -->
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-semibold">Informacje o serwerze</h2>
                <p class="mt-2"><strong>PHP:</strong> {{ phpversion() }}</p>
                <p class="mt-2"><strong>System:</strong> {{ php_uname() }}</p>
                <p class="mt-2"><strong>Certyfikat serwera:</strong>
                    @if(file_exists('app/certificates/server.crt')))
                        <span class="text-green-600">Wygenerowany</span>
                    @else
                        <span class="text-red-600">Nie istnieje</span>
                    @endif
                </p>
                <a href="{{ route('admin.certificate.form') }}" class="text-blue-500 mt-2 inline-block">Zarządzaj certyfikatem</a>
            </div>
        </div>

        <!-- Ostatnie logi -->
        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-xl font-semibold mb-4">Ostatnie logi aktywności</h2>
            @if($recentLogs->count())
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr>
                        <th class="border-b p-2">Użytkownik</th>
                        <th class="border-b p-2">Akcja</th>
                        <th class="border-b p-2">Data</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentLogs as $log)
                        <tr>
                            <td class="border-b p-2">{{ $log->causer?->name ?? 'System' }}</td>
                            <td class="border-b p-2">{{ $log->description }}</td>
                            <td class="border-b p-2">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p>Brak logów do wyświetlenia.</p>
            @endif
        </div>
    </div>
@endsection
