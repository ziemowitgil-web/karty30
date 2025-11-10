@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-4xl">

        <h1 class="text-3xl font-bold mb-6 text-gray-900">Szczegóły konsultacji #{{ $consultation->id }}</h1>

        {{-- Akcje --}}
        <div class="flex gap-3 mb-6">
            @if($consultation->status === 'draft')
                <form method="POST" action="{{ route('consultations.sign', $consultation) }}">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:ring-2 focus:ring-green-300 focus:outline-none">
                        Podpisz konsultację
                    </button>
                </form>
            @endif
            @if($consultation->sha1sum)
                <a href="{{ route('consultations.pdf', $consultation) }}" target="_blank"
                   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    Pobierz PDF
                </a>
                <a href="{{ route('consultations.xml', $consultation) }}" target="_blank"
                   class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 focus:ring-2 focus:ring-gray-300 focus:outline-none">
                    Pobierz XML
                </a>
            @endif
        </div>

        {{-- Szczegóły konsultacji --}}
        <div class="bg-white p-6 rounded shadow mb-6">
            <table class="w-full table-auto border-collapse">
                <tr>
                    <td class="font-medium py-2 px-4 border-b">Klient</td>
                    <td class="py-2 px-4 border-b">{{ $consultation->client->name ?? 'SYSTEM' }}</td>
                </tr>
                <tr>
                    <td class="font-medium py-2 px-4 border-b">Data i godzina</td>
                    <td class="py-2 px-4 border-b">{{ \Carbon\Carbon::parse($consultation->consultation_datetime)->format('d.m.Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="font-medium py-2 px-4 border-b">Czas trwania</td>
                    <td class="py-2 px-4 border-b">{{ $consultation->duration_minutes }} min</td>
                </tr>
                <tr>
                    <td class="font-medium py-2 px-4 border-b">Przeprowadził</td>
                    <td class="py-2 px-4 border-b">{{ $consultation->user->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="font-medium py-2 px-4 border-b">Opis / notatka</td>
                    <td class="py-2 px-4 border-b">{!! nl2br(e($consultation->description)) !!}</td>
                </tr>
                <tr>
                    <td class="font-medium py-2 px-4 border-b">Dalsze działania</td>
                    <td class="py-2 px-4 border-b">{{ $consultation->next_action ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="font-medium py-2 px-4 border-b">Status</td>
                    <td class="py-2 px-4 border-b">{{ ucfirst($consultation->status) }}</td>
                </tr>
                @if($consultation->sha1sum)
                    <tr>
                        <td class="font-medium py-2 px-4 border-b">SHA1</td>
                        <td class="py-2 px-4 border-b font-mono break-all">{{ $consultation->sha1sum }}</td>
                    </tr>
                @endif
            </table>
        </div>

        {{-- Historia aktywności --}}
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl font-semibold mb-4">Historia działań</h2>
            @if($consultation->activities->count())
                <ul class="divide-y divide-gray-200">
                    @foreach($consultation->activities as $activity)
                        <li class="py-2 flex justify-between items-center">
                            <span>{!! nl2br(e($activity->description)) !!}</span>
                            <span class="text-gray-500 text-sm">{{ $activity->created_at->format('d.m.Y H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500">Brak aktywności dla tej konsultacji.</p>
            @endif
        </div>
    </div>
@endsection
