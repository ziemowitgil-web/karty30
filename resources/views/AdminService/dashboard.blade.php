@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Panel administratora</h1>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-semibold">Użytkownicy</h2>
                <p class="text-3xl mt-2">{{ $userCount }}</p>
                <a href="{{ route('admin.users.list') }}" class="text-blue-500 mt-2 inline-block">Zarządzaj użytkownikami</a>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-semibold">Logi aktywności</h2>
                <p class="text-3xl mt-2">{{ $logCount }}</p>
                <a href="{{ route('admin.logs') }}" class="text-blue-500 mt-2 inline-block">Przeglądaj logi</a>
            </div>
        </div>
    </div>
@endsection
