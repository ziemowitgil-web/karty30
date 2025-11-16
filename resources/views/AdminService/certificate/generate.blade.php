@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Generowanie certyfikatu X.509 serwera</h1>

        @if(session('success'))
            <div class="bg-green-200 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-200 text-red-800 p-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.certificate.generate') }}" method="POST" class="bg-white shadow p-6 rounded">
            @csrf

            <div class="mb-4">
                <label class="block font-medium mb-1" for="common_name">Common Name (CN)</label>
                <input type="text" name="common_name" id="common_name" class="border rounded p-2 w-full" value="{{ old('common_name') }}" required>
                @error('common_name')
                <p class="text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1" for="organization">Organization (O)</label>
                <input type="text" name="organization" id="organization" class="border rounded p-2 w-full" value="{{ old('organization') }}">
                @error('organization')
                <p class="text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1" for="country">Country (C)</label>
                <input type="text" name="country" id="country" class="border rounded p-2 w-full" value="{{ old('country') }}" maxlength="2">
                @error('country')
                <p class="text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Generuj certyfikat
            </button>
        </form>
    </div>
@endsection
