@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-2xl">

        <h1 class="text-2xl font-bold mb-6">Dodaj nowy termin</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 border border-green-300 rounded p-3 mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('schedules.store') }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow-md">
            @csrf

            <!-- Wybór klienta -->
            <div>
                <label for="client_id" class="block text-gray-700 font-medium mb-1">Klient</label>
                <select name="client_id" id="client_id"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('client_id') border-red-500 @enderror">
                    <option value="">— Wybierz klienta —</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Data -->
            <div>
                <label for="date" class="block text-gray-700 font-medium mb-1">Data</label>
                <input type="date" name="date" id="date" value="{{ old('date') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('date') border-red-500 @enderror">
                @error('date')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Godzina -->
            <div>
                <label for="time" class="block text-gray-700 font-medium mb-1">Godzina</label>
                <input type="time" name="time" id="time" value="{{ old('time') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('time') border-red-500 @enderror">
                @error('time')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Czas trwania -->
            <div>
                <label for="duration_minutes" class="block text-gray-700 font-medium mb-1">Czas trwania (minuty)</label>
                <input type="number" name="duration_minutes" id="duration_minutes" min="1"
                       value="{{ old('duration_minutes') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('duration_minutes') border-red-500 @enderror">
                @error('duration_minutes')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-gray-700 font-medium mb-1">Rodzaj rezerwacji (status)</label>
                <select name="status" id="status"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                    <option value="">— Wybierz —</option>
                    <option value="preliminary" {{ old('status') == 'preliminary' ? 'selected' : '' }}>Wstępna</option>
                    <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Potwierdzona</option>
                </select>
                <p class="text-gray-500 text-sm mt-1">
                    <strong>Wyjaśnienie statusów:</strong><br>
                    <span class="font-medium">Wstępna</span> – Klient może jeszcze zmienić termin.<br>
                    <span class="font-medium">Potwierdzona</span> – Klient potwierdził termin, rezerwuj salę.
                </p>
                @error('status')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Opis -->
            <div>
                <label for="description" class="block text-gray-700 font-medium mb-1">Opis (opcjonalnie)</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                          placeholder="Dodatkowe informacje...">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Przycisk zapisu -->
            <div class="text-right">
                <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition font-medium">
                    Zapisz termin
                </button>
            </div>
        </form>
    </div>
@endsection
