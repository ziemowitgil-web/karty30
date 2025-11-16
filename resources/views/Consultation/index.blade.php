@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-3xl">
        <h1 id="pageTitle" class="text-3xl font-bold mb-6 text-gray-900">Dodaj konsultację</h1>

        <div id="ariaMessage" class="sr-only" aria-live="polite"></div>

        <div id="formAlert" class="sr-only text-green-700 bg-green-100 p-3 mb-4 rounded" role="alert" aria-live="polite"></div>

        <form id="consultationForm" method="POST" class="space-y-6 bg-white p-6 rounded shadow" novalidate>
            @csrf
            <input type="hidden" name="mode" id="mode_hidden" value="manual">
            <input type="hidden" name="duration_minutes" id="duration_minutes_hidden">

            {{-- Tryb konsultacji --}}
            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-1">Tryb konsultacji</label>
                <div class="flex gap-4">
                    <button type="button" class="modeBtn bg-blue-600 text-white px-4 py-2 rounded" data-mode="reservation">Rezerwacja</button>
                    <button type="button" class="modeBtn bg-gray-200 text-gray-800 px-4 py-2 rounded" data-mode="manual">Bez rezerwacji</button>
                    <button type="button" class="modeBtn bg-gray-200 text-gray-800 px-4 py-2 rounded" data-mode="pfron">Szkolenie PFRON</button>
                </div>
            </div>

            {{-- Rezerwacja --}}
            <div id="reservationSection" class="hidden">
                <label for="scheduleSelect" class="block font-medium text-gray-700 mb-1">Wybierz rezerwację</label>
                <select id="scheduleSelect" name="schedule_id" class="w-full border rounded p-2 mb-2">
                    <option value="">— Brak rezerwacji —</option>
                    @foreach($schedules as $s)
                        <option value="{{ $s->id }}" data-client="{{ $s->client_id }}" data-date="{{ \Carbon\Carbon::parse($s->start_time)->format('Y-m-d') }}" data-time="{{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}" data-duration="{{ $s->duration_minutes }}">
                            {{ $s->id }} — {{ $s->client->name ?? '-' }} — {{ \Carbon\Carbon::parse($s->start_time)->format('d.m.Y H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Klient --}}
            <div id="clientSection" class="mb-4">
                <label for="clientSelect" class="block font-medium text-gray-700 mb-1">Klient</label>
                <select id="clientSelect" name="client_id" class="w-full border rounded p-2">
                    <option value="">— Wybierz klienta —</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                    <option value="SYSTEM">SYSTEM</option>
                </select>
                <p id="clientError" class="text-red-600 text-sm mt-1 sr-only" aria-live="polite"></p>
            </div>

            {{-- Data, godzina, czas --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="consultation_date" class="block font-medium text-gray-700 mb-1">Data</label>
                    <input type="date" id="consultation_date" name="consultation_date" class="w-full border rounded p-2">
                    <p id="dateError" class="text-red-600 text-sm sr-only" aria-live="polite"></p>
                </div>
                <div>
                    <label for="consultation_time" class="block font-medium text-gray-700 mb-1">Godzina</label>
                    <input type="time" id="consultation_time" name="consultation_time" class="w-full border rounded p-2">
                    <p id="timeError" class="text-red-600 text-sm sr-only" aria-live="polite"></p>
                </div>
                <div>
                    <label for="duration_hours" class="block font-medium text-gray-700 mb-1">Czas trwania (h)</label>
                    <input type="number" id="duration_hours" min="0.25" max="24" step="0.25" class="w-full border rounded p-2">
                    <p id="durationError" class="text-red-600 text-sm sr-only" aria-live="polite"></p>
                </div>
            </div>

            <p id="availabilityError" class="text-red-600 text-sm sr-only" aria-live="polite"></p>

            {{-- Dalsze działania i opis --}}
            <div>
                <label for="next_action" class="block font-medium text-gray-700 mb-1">Dalsze działania</label>
                <input type="text" id="next_action" name="next_action" class="w-full border rounded p-2 mb-2">

                <label for="description" class="block font-medium text-gray-700 mb-1">Opis / notatka</label>
                <textarea id="description" name="description" rows="3" class="w-full border rounded p-2"></textarea>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Zapisz i podpisz</button>

            <p class="text-xs text-gray-500 mt-2">Konsultacja zostanie od razu podpisana elektronicznie po zapisaniu.</p>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modeBtns = document.querySelectorAll('.modeBtn');
            const reservationSection = document.getElementById('reservationSection');
            const clientSelect = document.getElementById('clientSelect');
            const modeHidden = document.getElementById('mode_hidden');

            const scheduleSelect = document.getElementById('scheduleSelect');
            const consultationDate = document.getElementById('consultation_date');
            const consultationTime = document.getElementById('consultation_time');
            const durationHours = document.getElementById('duration_hours');
            const durationHidden = document.getElementById('duration_minutes_hidden');
            const availabilityError = document.getElementById('availabilityError');
            const formAlert = document.getElementById('formAlert');

            // tryby
            modeBtns.forEach(btn => btn.addEventListener('click', () => {
                const mode = btn.dataset.mode;
                modeHidden.value = mode;

                modeBtns.forEach(b => {
                    b.classList.remove('bg-blue-600','text-white');
                    b.classList.add('bg-gray-200','text-gray-800');
                });
                btn.classList.add('bg-blue-600','text-white');

                reservationSection.style.display = (mode==='reservation') ? 'block' : 'none';
                clientSelect.disabled = (mode==='reservation');
            }));

            // auto-fill rezerwacji
            scheduleSelect?.addEventListener('change', () => {
                const sel = scheduleSelect.selectedOptions[0];
                if(!sel.value) return;
                clientSelect.value = sel.dataset.client;
                consultationDate.value = sel.dataset.date;
                consultationTime.value = sel.dataset.time;
                durationHours.value = (sel.dataset.duration/60).toFixed(2);
                durationHidden.value = sel.dataset.duration;
            });

            // godziny -> minuty
            durationHours.addEventListener('input', () => {
                const h = parseFloat(durationHours.value);
                durationHidden.value = !isNaN(h) ? Math.round(h*60) : '';
            });

            // AJAX check availability + podpis od razu
            document.getElementById('consultationForm').addEventListener('submit', async function(e){
                e.preventDefault();
                availabilityError.classList.add('sr-only');
                formAlert.classList.add('sr-only');

                const client_id = clientSelect.value;
                const start = consultationDate.value+' '+consultationTime.value;
                const dur = parseInt(durationHidden.value);

                if(!client_id || !start || !dur) {
                    alert('Uzupełnij wymagane pola.');
                    return;
                }

                try {
                    // check availability
                    const checkRes = await fetch("{{ route('schedules.checkAvailability') }}", {
                        method:'POST',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                        body: JSON.stringify({client_id,start_time:start,duration_minutes:dur})
                    });
                    const data = await checkRes.json();
                    if(!data.available){
                        availabilityError.textContent='Klient ma już termin w tym czasie.';
                        availabilityError.classList.remove('sr-only');
                        availabilityError.focus();
                        return;
                    }

                    // save + podpis
                    const formData = new FormData(this);
                    const saveRes = await fetch("{{ route('consultations.store') }}", {
                        method:'POST',
                        headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'},
                        body: formData
                    });
                    const saveData = await saveRes.json();
                    if(saveData.success){
                        formAlert.textContent='Konsultacja zapisana i podpisana!';
                        formAlert.classList.remove('sr-only');
                        this.reset();
                    } else {
                        alert('Błąd: '+(saveData.message||'Nie udało się zapisać.'));
                    }

                } catch(err){ console.error(err); alert('Błąd serwera.'); }
            });
        });
    </script>
@endsection
