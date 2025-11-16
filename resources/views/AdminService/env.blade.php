@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-6 py-6">
        <h1 class="text-2xl font-bold mb-4">Edytuj zmienne .env</h1>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.env.update') }}">
            @csrf
            <div class="mb-4">
                <label class="block font-semibold mb-1">Klucz zmiennej</label>
                <input type="text" name="key" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Wartość</label>
                <input type="text" name="value" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Zaktualizuj</button>
        </form>
    </div>
@endsection
