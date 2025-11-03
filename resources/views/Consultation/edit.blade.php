@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Edytuj konsultację</h1>

        <!-- Informacja dla użytkownika -->
        <div class="mb-6 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
            Po edycji konsultacji należy ponownie wydrukować kartę konsultacji i podpisać ją.
        </div>

        <form action="{{ route('consultations.update', $consultation) }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow-md">
            @csrf
            @method('PUT')

            <!-- Klient (tylko do podglądu, nieedytowalny) -->
            <div>
                <label class="block font-medium text-gray-700">Klient</label>
                <input type="text" value="{{ $consultation->client->name }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" disabled>
            </div>

            <!-- Data konsultacji (tylko dzisiaj i przyszłość) -->
            <div>
                <label for="consultation_date" class="block font-medium text-gray-700">Data konsultacji</label>
                <input type="date" name="consultation_date" id="consultation_date"
                       value="{{ old('consultation_date', \Carbon\Carbon::parse($consultation->consultation_datetime)->format('Y-m-d')) }}"
                       min="{{ date('Y-m-d') }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @error('consultation_date')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Godzina -->
            <div>
                <label for="consultation_time" class="block font-medium text-gray-700">Godzina</label>
                <input type="time" name="consultation_time" id="consultation_time"
                       value="{{ old('consultation_time', \Carbon\Carbon::parse($consultation->consultation_datetime)->format('H:i')) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @error('consultation_time')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Czas trwania -->
            <div>
                <label for="duration_minutes" class="block font-medium text-gray-700">Czas trwania (minuty)</label>
                <input type="number" name="duration_minutes" id="duration_minutes"
                       value="{{ old('duration_minutes', $consultation->duration_minutes) }}" min="1"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @error('duration_minutes')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Opis -->
            <div>
                <label for="description" class="block font-medium text-gray-700">Opis</label>
                <textarea name="description" id="description" rows="4"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $consultation->description) }}</textarea>
                @error('description')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="planned" {{ old('status', $consultation->status) == 'planned' ? 'selected' : '' }}>Zaplanowana</option>
                    <option value="completed" {{ old('status', $consultation->status) == 'completed' ? 'selected' : '' }}>Zrealizowana</option>
                    <option value="cancelled" {{ old('status', $consultation->status) == 'cancelled' ? 'selected' : '' }}>Anulowana</option>
                </select>
                @error('status')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Nie licz godzin -->
            <div class="flex items-center space-x-2 mt-2">
                <input type="checkbox" name="skip_hours" id="skip_hours" value="1"
                       {{ old('skip_hours', false) ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <label for="skip_hours" class="text-gray-700">Nie licz godzin</label>
            </div>

            <!-- Przyciski -->
            <div class="flex justify-end space-x-2 mt-4">
                <a href="{{ route('consultations.index') }}"
                   class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Anuluj</a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Zapisz</button>
            </div>
        </form>
    </div>
@endsection
