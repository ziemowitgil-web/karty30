@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-6 py-6">
        <h1 class="text-2xl font-bold mb-4">Lista użytkowników</h1>

        {{-- Pasek wyszukiwania --}}
        <form method="GET" action="{{ route('admin.users.list') }}" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Szukaj po imieniu, emailu lub numerze dokumentu"
                   class="border rounded px-3 py-2 flex-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Szukaj</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-gray-800 text-white text-left">
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Imię i nazwisko</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Numer dokumentu</th>
                    <th class="px-4 py-2">Typ dokumentu</th>
                    <th class="px-4 py-2">Akcje</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-4 py-2">{{ $user->id }}</td>
                        <td class="px-4 py-2">{{ $user->name }}</td>
                        <td class="px-4 py-2">{{ $user->email }}</td>
                        <td class="px-4 py-2">{{ $user->document_number ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $user->document_type ?? '-' }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">Edytuj</a>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Na pewno chcesz usunąć użytkownika?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Usuń</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">Brak użytkowników.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links('pagination::tailwind') }}
        </div>
    </div>
@endsection
