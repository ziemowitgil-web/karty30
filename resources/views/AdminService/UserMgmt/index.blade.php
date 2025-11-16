@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-6 py-6">

        <!-- Nagłówek i wyszukiwarka -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-4">
            <h1 class="text-2xl font-bold mb-2 md:mb-0">Lista użytkowników</h1>

            <form action="{{ route('admin.users.list') }}" method="GET" class="flex gap-2 mb-2 md:mb-0">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Szukaj po nazwie, emailu lub dokumencie"
                       class="px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Szukaj
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.users.list') }}"
                       class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                        Wyczyść
                    </a>
                @endif
            </form>
        </div>

        <!-- Komunikaty sukcesu -->
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tabela użytkowników -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-gray-800 text-white text-left">
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Imię i nazwisko</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Dokument</th>
                    <th class="px-4 py-2">WebAuthn</th>
                    <th class="px-4 py-2">Certyfikat</th>
                    <th class="px-4 py-2">Akcje</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $user->id }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $user->name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $user->email }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">
                            {{ $user->document_type ?? '-' }}<br>
                            {{ $user->document_number ?? '-' }}<br>
                            {{ $user->document_issuer ?? '-' }}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700">
                            @if($user->hasWebauthnKey())
                                <span class="px-2 py-1 bg-green-200 text-green-800 rounded text-xs">Tak</span>
                            @else
                                <span class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">Nie</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700">
                            @if($user->activeCertificate)
                                <span class="px-2 py-1 bg-blue-200 text-blue-800 rounded text-xs">
                                    Aktywny
                                </span>
                            @else
                                <span class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">
                                    Brak
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700 flex gap-2">
                            <a href="{{ route('users.edit', $user) }}"
                               class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition text-xs">
                                Edytuj
                            </a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Na pewno chcesz usunąć tego użytkownika?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition text-xs">
                                    Usuń
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500">Brak użytkowników.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginacja -->
        <div class="mt-4">
            {{ $users->links('pagination::tailwind') }}
        </div>
    </div>
@endsection
