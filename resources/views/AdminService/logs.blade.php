@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Logi aktywności</h1>

        <form action="{{ route('admin.logs.clear') }}" method="POST" class="mb-4">
            @csrf
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Wyczyść logi</button>
        </form>

        <table class="min-w-full bg-white border">
            <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">ID</th>
                <th class="border px-4 py-2">Użytkownik</th>
                <th class="border px-4 py-2">Akcja</th>
                <th class="border px-4 py-2">Data</th>
            </tr>
            </thead>
            <tbody>
            @foreach($logs as $log)
                <tr>
                    <td class="border px-4 py-2">{{ $log->id }}</td>
                    <td class="border px-4 py-2">{{ $log->causer?->name ?? 'System' }}</td>
                    <td class="border px-4 py-2">{{ $log->description }}</td>
                    <td class="border px-4 py-2">{{ $log->created_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
