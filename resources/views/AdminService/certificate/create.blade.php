@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Generowanie certyfikatu serwera</h1>

        @if($certificateExists)
            <div class="bg-green-100 p-4 rounded mb-4">
                <h2 class="font-semibold">Aktualny certyfikat:</h2>
                <pre>{{ print_r($certificateInfo, true) }}</pre>
            </div>
        @endif

        <form action="{{ route('admin.certificate.generate') }}" method="POST" class="bg-white p-4 rounded shadow">
            @csrf
            <div class="mb-4">
                <label class="block font-semibold mb-2">Nazwa serwera / CN</label>
                <input type="text" name="common_name" class="border p-2 w-full" required>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-2">Organizacja (O)</label>
                <input type="text" name="organization" class="border p-2 w-full">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-2">Jednostka organizacyjna (OU)</label>
                <input type="text" name="organizational_unit" class="border p-2 w-full">
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Generuj certyfikat</button>
        </form>
    </div>
@endsection
