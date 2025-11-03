@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-2xl">

        <h1 class="text-2xl font-bold mb-6">Zmień termin konsultacji </h1>

        <form action="{{ route('schedules.schedules.updateReschedule', $schedule) }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow-md">
            @csrf
            @method('PATCH')

            <!-- Klient -->
            <div>
                <label for="client_id" class="block text-gray-700 font-medium mb-1">Klient</label>
                <select name="client_id" id="client_id"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('client_id') border-red-500 @enderror">
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ $schedule->client_id == $client->id ? 'selected' : '' }}>
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
                <input type="date" name="date" id="date"
                       value="{{ old('date', $schedule->start_time->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('date') border-red-500 @enderror">
                @error('date')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Godzina -->
            <div>
                <label for="time" class="block text-gray-700 font-medium mb-1">Godzina</label>
                <input type="time" name="time" id="time"
                       value="{{ old('time', $schedule->start_time->format('H:i')) }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('time') border-red-500 @enderror">
                @error('time')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>


            <!-- Przycisk -->
            <div class="text-right">
                <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition font-medium">
                    Zapisz zmiany
                </button>
            </div>
        </form>
    </div>
@endsection
