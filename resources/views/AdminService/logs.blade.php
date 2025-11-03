@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-6 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-4">
            <h1 class="text-2xl font-bold mb-2 md:mb-0">Logi aktywności</h1>
            <form action="{{ route('logs.clear') }}" method="POST" onsubmit="return confirm('Na pewno chcesz wyczyścić wszystkie logi?');">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                    Wyczyść logi
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-gray-800 text-white text-left">
                <tr>
                    <th class="px-4 py-2">Data</th>
                    <th class="px-4 py-2">Użytkownik</th>
                    <th class="px-4 py-2">Akcja</th>
                    <th class="px-4 py-2">Model</th>
                    <th class="px-4 py-2">Więcej</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $log->causer ? $log->causer->name : 'System' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $log->description }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $log->subject_type ? class_basename($log->subject_type) : '-' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">
                            @if($log->properties->count())
                                <button type="button" onclick="alert(JSON.stringify(@json($log->properties->toArray()), null, 2))"
                                        class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition text-xs">
                                    Pokaż szczegóły
                                </button>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">Brak logów.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links('pagination::tailwind') }}
        </div>
    </div>
@endsection
