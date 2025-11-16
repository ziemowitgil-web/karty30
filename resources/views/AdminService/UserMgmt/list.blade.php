@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">Lista użytkowników</h1>
            <a href="{{ route('admin.users.create') }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Dodaj użytkownika</a>
        </div>

        <form method="GET" class="mb-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Szukaj..." class="border px-2 py-1 rounded">
            <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded">Szukaj</button>
        </form>

        <table class="min-w-full bg-white border">
            <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">ID</th>
                <th class="border px-4 py-2">Imię</th>
                <th class="border px-4 py-2">Email</th>
                <th class="border px-4 py-2">Dokument</th>
                <th class="border px-4 py-2">Akcje</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td class="border px-4 py-2">{{ $user->id }}</td>
                    <td class="border px-4 py-2">{{ $user->name }}</td>
                    <td class="border px-4 py-2">{{ $user->email }}</td>
                    <td class="border px-4 py-2">{{ $user->document_number }}</td>
                    <td class="border px-4 py-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-500 mr-2">Edytuj</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Na pewno usunąć?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500">Usuń</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection
