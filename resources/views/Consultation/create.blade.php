@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-4xl">

        <h1 id="pageTitle" class="text-3xl font-bold mb-6 text-gray-900">Dodaj konsultację</h1>

        {{-- Tryb kreatora --}}
        <div class="flex gap-4 mb-6">
            <button type="button" class="modeBtn px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none"
                    data-mode="reservation" aria-pressed="true">
                Klient z rezerwacji
            </button>
            <button type="button" class="modeBtn px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:ring-2 focus:ring-gray-300 focus:outline-none"
                    data-mode="manual" aria-pressed="false">
                Bez rezerwacji
            </button>
            <button type="button" class="modeBtn px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:ring-2 focus:ring-gray-300 focus:outline-none"
                    data-mode="pfron" aria-pressed="false">
                Szkolenie PFRON
            </button>
        </div>

        {{-- Komunikaty dla czytników --}}
        <div id="ariaMessage" class="sr-only" aria-live="polite"></div>

        {{-- Formularz --}}
        <form id="consultationForm" action="{{ route('consultations.store') }}" method="POST" class="space-y-6 bg-white p-6 rounded shadow" role="form" aria-labelledby="pageTitle" novalidate>
            @csrf
            <input type="hidden" name="status" value="draft">
            <input type="hidden" name="duration_minutes" id="duration_minutes_hidden">

            {{-- Pole rezerwacji --}}
            <div id="reservationField">
                <label for="scheduleSelect" class="block text-gray-700 font-medium mb-1">Wybierz rezerwację</label>
                <select id="scheduleSelect" name="schedule_id" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
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

            {{-- Pole klienta --}}
            <div id="clientField">
                <label for="clientSelect" class="block text-gray-700 font-medium mb-1">Klient <span aria-hidden="true">*</span></label>
                <select id="clientSelect" name="client_id" required aria-required="true" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">— Wybierz klienta —</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                    <option value="SYSTEM">SYSTEM</option>
                </select>
            </div>

            {{-- Data i godzina --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="consultation_date" class="block text-gray-700 font-medium mb-1">Data konsultacji <span aria-hidden="true">*</span></label>
                    <input type="date" id="consultation_date" name="consultation_date" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required aria-required="true">
                </div>
                <div>
                    <label for="consultation_time" class="block text-gray-700 font-medium mb-1">Godzina rozpoczęcia <span aria-hidden="true">*</span></label>
                    <input type="time" id="consultation_time" name="consultation_time" class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required aria-required="true">
                </div>
            </div>

            {{-- Czas trwania --}}
            <div>
                <label for="duration_hours" class="block text-gray-700 font-medium mb-1">Czas trwania (w godzinach) <span aria-hidden="true">*</span></label>
                <input type="number" id="duration_hours" min="0.25" max="24" step="0.25" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="np. 1.5" required aria-required="true">
                <p class="text-gray-500 text-sm mt-1">System automatycznie przeliczy godziny na minuty.</p>
            </div>

            {{-- Dalsze działania --}}
            <div>
                <label for="next_action" class="block text-gray-700 font-medium mb-1">Dalsze działania</label>
                <input type="text" id="next_action" name="next_action" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" maxlength="255" placeholder="Opcjonalnie">
            </div>

            {{-- Opis --}}
            <div>
                <label for="description" class="block text-gray-700 font-medium mb-1">Opis / notatka</label>
                <textarea id="description" name="description" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" aria-multiline="true" placeholder="Dodatkowe informacje..."></textarea>
            </div>

            {{-- Zapis roboczo --}}
            <div class="flex justify-end mt-4">
                <button type="button" onclick="confirmDraft()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none transition" aria-describedby="saveHelp">
                    Zapisz roboczo
                </button>
            </div>
            <p id="saveHelp" class="sr-only">Po kliknięciu zostanie otwarte okno potwierdzenia.</p>
        </form>
    </div>

    <script>
        const modeBtns = document.querySelectorAll('.modeBtn');
        const reservationField = document.getElementById('reservationField');
        const clientField = document.getElementById('clientField');
        const clientSelect = document.getElementById('clientSelect');
        const scheduleSelect = document.getElementById('scheduleSelect');
        const consultationDate = document.getElementById('consultation_date');
        const consultationTime = document.getElementById('consultation_time');
        const durationHours = document.getElementById('duration_hours');
        const durationMinutesHidden = document.getElementById('duration_minutes_hidden');
        const ariaMessage = document.getElementById('ariaMessage');

        function showAriaMessage(message){ if(ariaMessage) ariaMessage.textContent = message; }

        modeBtns.forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const mode = btn.dataset.mode;
                modeBtns.forEach(b=>{
                    b.classList.remove('bg-blue-600','text-white');
                    b.classList.add('bg-gray-200','text-gray-800');
                    b.setAttribute('aria-pressed','false');
                });
                btn.classList.add('bg-blue-600','text-white'); btn.setAttribute('aria-pressed','true');

                switch(mode){
                    case 'reservation':
                        reservationField.style.display = 'block';
                        clientSelect.disabled = true;
                        showAriaMessage("Tryb: Klient z rezerwacji. Wybierz rezerwację, aby automatycznie uzupełnić dane.");
                        break;
                    case 'manual':
                        reservationField.style.display = 'none';
                        clientSelect.disabled = false;
                        showAriaMessage("Tryb: Bez rezerwacji. Wybierz klienta ręcznie.");
                        break;
                    case 'pfron':
                        reservationField.style.display = 'none';
                        clientSelect.disabled = false;
                        showAriaMessage("Tryb: Szkolenie PFRON (mockup). Wybierz klienta i uzupełnij dane.");
                        break;
                }
            });
        });

        // Domyślnie tryb z rezerwacją
        document.querySelector('[data-mode="reservation"]').click();

        // Autouzupełnianie danych po wyborze rezerwacji
        scheduleSelect.addEventListener('change', function(){
            const selected = this.options[this.selectedIndex];
            if(!selected.value) return;
            clientSelect.value = selected.dataset.client;
            consultationDate.value = selected.dataset.date;
            consultationTime.value = selected.dataset.time;
            durationHours.value = (selected.dataset.duration / 60).toFixed(2);
            showAriaMessage("Dane automatycznie uzupełnione z wybranej rezerwacji.");
        });

        // Konwersja godzin na minuty
        durationHours.addEventListener('input', ()=>{
            const hours = parseFloat(durationHours.value);
            if(!isNaN(hours)) durationMinutesHidden.value = Math.round(hours*60);
        });

        // Zapis roboczo
        function confirmDraft(){
            if(!clientSelect.value){
                alert("Wybierz klienta, zanim zapiszesz konsultację.");
                clientSelect.focus();
                return;
            }
            const confirmSave = confirm("Czy chcesz zapisać konsultację jako wersję roboczą?");
            if(confirmSave){
                const hours = parseFloat(durationHours.value);
                if(!isNaN(hours)) durationMinutesHidden.value = Math.round(hours*60);
                document.getElementById('consultationForm').submit();
            }
        }
    </script>
@endsection
