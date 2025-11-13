@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-6xl">

        <h1 class="text-3xl font-extrabold mb-6 text-gray-800">Czarna lista klientów</h1>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg shadow">
                {{ session('success') }}
            </div>
        @endif

        @if($clients->isEmpty())
            <p class="text-gray-500 text-center">Brak klientów na czarnej liście.</p>
        @else
            <div class="overflow-x-auto shadow-md rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Imię i nazwisko</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Powód</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">Data dodania</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">Akcje</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($clients as $client)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-gray-700 font-medium">{{ $client->id }}</td>
                            <td class="px-4 py-3 text-gray-700 font-medium">{{ $client->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $client->reason }}</td>
                            <td class="px-4 py-3 text-gray-600 text-center">
                                {{ \Carbon\Carbon::parse($client->created_at)->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form action="{{ route('schedules.client_blacklist.destroy', $client->id) }}" method="POST"
                                      onsubmit="return confirm('Na pewno chcesz usunąć klienta z czarnej listy?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600 text-sm font-medium transition">
                                        Usuń
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-6 text-center">
            <a href="{{ route('raport') }}"
               class="inline-block bg-gray-700 text-white px-6 py-3 rounded-lg shadow hover:bg-gray-800 transition font-medium">
                Powrót do raportów
            </a>
        </div>
    </div>
@endsection
