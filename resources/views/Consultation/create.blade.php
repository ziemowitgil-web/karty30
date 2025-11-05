@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-4xl">
        <h1 class="text-3xl font-bold mb-6 text-gray-900">Dodaj konsultację</h1>

        {{-- Komunikaty --}}
        @if(session('error'))
            <div id="alert" class="flex items-center p-4 mb-4 text-red-800 border border-red-300 rounded-lg bg-red-50 animate-fade-in" role="alert">
                <svg class="flex-shrink-0 w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 109.5 9.5A9.51 9.51 0 0010 .5zM9 5a1 1 0 012 0v5a1 1 0 01-2 0zm1 8a1.25 1.25 0 111.25-1.25A1.25 1.25 0 0110 13z"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div id="alert" class="flex items-center p-4 mb-4 text-green-800 border border-green-300 rounded-lg bg-green-50 animate-fade-in" role="alert">
                <svg class="flex-shrink-0 w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div id="alert" class="p-4 mb-4 text-red-800 border border-red-300 rounded-lg bg-red-50 animate-fade-in">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formularz --}}
        <form id="consultationForm" action="{{ route('consultations.store') }}" method="POST" class="space-y-6 bg-white p-6 rounded shadow">
            @csrf
            <input type="hidden" name="status" value="draft">

            <!-- Rezerwacja -->
            <div>
                <label for="scheduleSelect" class="block text-gray-700 font-medium mb-1">Wybierz rezerwację (opcjonalnie)</label>
                <select id="scheduleSelect" name="schedule_id" class="w-full border border-gray-300 rounded p-2">
                    <option value="">— Brak rezerwacji —</option>
                    @foreach($schedules as $s)
                        <option value="{{ $s->id }}"
                                data-client="{{ $s->client_id }}"
                                data-date="{{ \Carbon\Carbon::parse($s->start_time)->format('Y-m-d') }}"
                                data-time="{{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}"
                                data-duration="{{ $s->duration_minutes }}">
                            {{ $s->id }} — {{ $s->client->name ?? 'Brak klienta' }} — {{ \Carbon\Carbon::parse($s->start_time)->format('d.m.Y H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Klient -->
            <div>
                <label for="clientSelect" class="block text-gray-700 font-medium mb-1">Klient *</label>
                <select id="clientSelect" name="client_id" class="w-full border border-gray-300 rounded p-2" required>
                    <option value="">— Wybierz klienta —</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                    <option value="SYSTEM">SYSTEM</option>
                </select>
            </div>

            <!-- Data i godzina -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="consultation_date" class="block text-gray-700 font-medium mb-1">Data konsultacji *</label>
                    <input type="date" id="consultation_date" name="consultation_date" class="w-full border border-gray-300 rounded p-2" required>
                </div>
                <div>
                    <label for="consultation_time" class="block text-gray-700 font-medium mb-1">Godzina rozpoczęcia *</label>
                    <input type="time" id="consultation_time" name="consultation_time" class="w-full border border-gray-300 rounded p-2" required>
                </div>
            </div>

            <!-- Czas trwania i dalsze działania -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="duration_minutes" class="block text-gray-700 font-medium mb-1">Czas trwania (minuty) *</label>
                    <input type="number" id="duration_minutes" name="duration_minutes" min="15" max="1440" class="w-full border border-gray-300 rounded p-2" required>
                </div>
                <div>
                    <label for="next_action" class="block text-gray-700 font-medium mb-1">Dalsze działania</label>
                    <input type="text" id="next_action" name="next_action" class="w-full border border-gray-300 rounded p-2" maxlength="255" placeholder="Opcjonalnie">
                </div>
            </div>

            <!-- Opis -->
            <div>
                <label for="description" class="block text-gray-700 font-medium mb-1">Opis / notatka</label>
                <textarea id="description" name="description" rows="3" class="w-full border border-gray-300 rounded p-2"></textarea>
            </div>

            <!-- Zapis roboczo -->
            <div class="flex justify-end mt-4">
                <button type="button" onclick="confirmDraft()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Zapisz roboczo</button>
            </div>
        </form>
    </div>

    <!-- Modal potwierdzenia -->
    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white p-6 rounded shadow-lg max-w-md w-full">
            <h2 class="text-xl font-semibold mb-4">Potwierdź dane konsultacji</h2>
            <p class="mb-4">Upewnij się, że wszystkie dane są poprawne. Konsultacja zostanie zapisana jako <strong>wersja robocza</strong>.</p>
            <div class="flex justify-end gap-4">
                <button onclick="closeModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Anuluj</button>
                <button onclick="submitDraft()" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Potwierdź</button>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
        .animate-fade-in { animation: fadeIn 0.4s ease-in-out; }
    </style>

    <script>
        const scheduleSelect = document.getElementById('scheduleSelect');
        const clientSelect = document.getElementById('clientSelect');
        const consultationDate = document.getElementById('consultation_date');
        const consultationTime = document.getElementById('consultation_time');
        const durationMinutes = document.getElementById('duration_minutes');

        // Automatyczne wypełnianie z rezerwacji
        scheduleSelect.addEventListener('change', function(){
            const selected = this.options[this.selectedIndex];
            if(!selected.value) return;
            clientSelect.value = selected.dataset.client;
            consultationDate.value = selected.dataset.date;
            consultationTime.value = selected.dataset.time;
            durationMinutes.value = selected.dataset.duration;
        });

        function confirmDraft(){
            if(!clientSelect.value || !consultationDate.value || !consultationTime.value || !durationMinutes.value){
                showToast('Proszę wypełnić wszystkie wymagane pola.', 'error');
                return;
            }
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeModal(){
            document.getElementById('confirmModal').classList.add('hidden');
        }

        function submitDraft(){
            document.querySelector('input[name="status"]').value = 'draft';
            document.getElementById('consultationForm').submit();
        }

        // Automatyczne chowanie komunikatów
        const alertBox = document.getElementById('alert');
        if (alertBox) {
            setTimeout(() => alertBox.classList.add('opacity-0', 'transition-opacity', 'duration-700'), 4000);
            setTimeout(() => alertBox.remove(), 4700);
        }

        // Mały toast dla błędów JS
        function showToast(message, type = 'info') {
            const bg = type === 'error' ? 'bg-red-600' : 'bg-green-600';
            const toast = document.createElement('div');
            toast.className = `${bg} text-white px-4 py-2 rounded shadow fixed bottom-4 right-4 animate-fade-in`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
    </script>
@endsection
