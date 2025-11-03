@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Zmień status konsultacji</h1>

        <div class="mb-4 text-gray-700">
            <p><strong>Klient:</strong> {{ $consultation->client->name }}</p>
            <p><strong>Data i godzina:</strong> {{ \Carbon\Carbon::parse($consultation->consultation_datetime)->format('d.m.Y H:i') }}</p>
            <p><strong>Czas trwania:</strong> {{ $consultation->duration_minutes }} min</p>
            <p class="mt-2 text-red-600 font-semibold">
                Po zmianie statusu należy ponownie wydrukować kartę i podpisać!
            </p>
        </div>

        <form action="{{ route('consultations.update_status', $consultation) }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow-md">
            @csrf
            @method('PATCH')

            <!-- Status -->
            <div>
                <label for="status" class="block font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="planned" {{ $consultation->status == 'planned' ? 'selected' : '' }}>Zaplanowana</option>
                    <option value="completed" {{ $consultation->status == 'completed' ? 'selected' : '' }}>Zrealizowana</option>
                    <option value="cancelled" {{ $consultation->status == 'cancelled' ? 'selected' : '' }}>Anulowana</option>
                </select>
                @error('status')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Powód zmiany -->
            <div>
                <label for="reason" class="block font-medium text-gray-700">Powód zmiany statusu</label>
                <textarea name="reason" id="reason" rows="3" required
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('reason') }}</textarea>
                @error('reason')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Nie licz godzin -->
            <div class="flex items-center mt-2">
                <input type="checkbox" name="skip_hours" id="skip_hours" class="mr-2">
                <label for="skip_hours" class="text-gray-700">Nie licz godzin (pomija aktualizację wykorzystanych godzin)</label>
            </div>

            <!-- Przyciski -->
            <div class="flex justify-end space-x-2 mt-4">
                <a href="{{ route('consultations.index') }}" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Zapisz zmiany</button>
            </div>
        </form>
    </div>
@endsection
