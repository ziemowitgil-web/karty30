@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-3xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Szczegóły konsultacji #{{ $id }}</h1>
            <a href="{{ route('consultations.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Powrót</a>
        </div>

        <div class="bg-white p-6 rounded shadow space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="font-medium text-gray-700">Klient:</span> {{ $client_name }}</div>
                <div><span class="font-medium text-gray-700">Użytkownik:</span> {{ $user_name }}</div>
                <div><span class="font-medium text-gray-700">Data i godzina:</span> {{ \Carbon\Carbon::parse($consultation_datetime)->format('d.m.Y H:i') }}</div>
                <div><span class="font-medium text-gray-700">Czas trwania:</span> {{ intdiv($duration_minutes,60) }}h {{ $duration_minutes % 60 }}m</div>
                <div><span class="font-medium text-gray-700">Tryb:</span> {{ ucfirst($mode) }}</div>
                <div class="md:col-span-2"><span class="font-medium text-gray-700">Dalsze działania:</span> {{ $next_action ?? '-' }}</div>
                <div class="md:col-span-2"><span class="font-medium text-gray-700">Opis / notatka:</span> <p class="mt-1">{{ $description ?? '-' }}</p></div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <h2 class="text-xl font-semibold mb-2">Status podpisu</h2>
                @if($status === 'completed')
                    <p class="text-green-600 font-semibold">Podpisana</p>
                    <p>Podpisana przez: <strong>{{ $approved_by_name }}</strong></p>
                    <p>SHA1: <code>{{ $sha1sum }}</code></p>
                    <p>Data podpisu: {{ \Carbon\Carbon::parse($approved_at)->format('d.m.Y H:i') }}</p>
                @else
                    <p class="text-red-600 font-semibold">Niepodpisana</p>
                @endif
            </div>

            @if($xmlData)
                <div class="border-t border-gray-200 pt-4">
                    <h2 class="text-xl font-semibold mb-2">Dane XML</h2>
                    <pre class="bg-gray-100 p-3 rounded overflow-auto text-sm">{{ $xmlData->asXML() }}</pre>
                </div>
            @endif

            <div class="flex gap-2 mt-4">
                @if($status !== 'completed')
                    <form action="{{ route('consultations.sign', $id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Podpisz konsultację</button>
                    </form>
                @else
                    <a href="{{ route('consultations.pdf', $id) }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Pobierz PDF</a>
                    <a href="{{ route('consultations.xml', $id) }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Pobierz XML</a>
                @endif
            </div>
        </div>
    </div>
@endsection
