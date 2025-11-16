@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Edytuj użytkownika</h1>

        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="mb-2">
                <label class="block mb-1">Imię</label>
                <input type="text" name="name" value="{{ $user->name }}" class="border px-2 py-1 w-full" required>
            </div>
            <div class="mb-2">
                <label class="block mb-1">Email</label>
                <input type="email" name="email" value="{{ $user->email }}" class="border px-2 py-1 w-full" required>
            </div>
            <div class="mb-2">
                <label class="block mb-1">Numer dokumentu</label>
                <input type="text" name="document_number" value="{{ $user->document_number }}" class="border px-2 py-1 w-full">
            </div>
            <div class="mb-2">
                <label class="block mb-1">Typ dokumentu</label>
                <input type="text" name="document_type" value="{{ $user->document_type }}" class="border px-2 py-1 w-full">
            </div>
            <div class="mb-2">
                <label class="block mb-1">Wydawca dokumentu</label>
                <input type="text" name="document_issuer" value="{{ $user->document_issuer }}" class="border px-2 py-1 w-full">
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Zaktualizuj</button>
        </form>
    </div>
@endsection
