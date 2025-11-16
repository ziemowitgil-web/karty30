@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 max-w-4xl">
        <h1 id="pageTitle" class="text-3xl font-bold mb-6 text-gray-900">Dodaj konsultację – Kreator</h1>

        <form id="consultationWizard" action="{{ route('consultations.store') }}" method="POST"
              class="space-y-6 bg-white p-6 rounded shadow" role="form" aria-labelledby="pageTitle" novalidate>
            @csrf
            <input type="hidden" name="duration_minutes" id="duration_minutes_hidden">

            {{-- Krok 1: Tryb konsultacji --}}
            <fieldset class="wizard-step" data-step="1">
                <legend class="text-xl font-semibold mb-4">1. Wybierz tryb konsultacji</legend>
                <div class="flex gap-4 mb-6">
                    <button type="button" class="modeBtn px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none"
                            data-mode="reservation" aria-pressed="true">Klient z rezerwacji</button>
                    <button type="button" class="modeBtn px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:ring-2 focus:ring-gray-300 focus:outline-none"
                            data-mode="manual" aria-pressed="false">Bez rezerwacji</button>
                    <button type="button" class="modeBtn px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:ring-2 focus:ring-gray-300 focus:outline-none"
                            data-mode="pfron" aria-pressed="false">Szkolenie PFRON</button>
                </div>
                <div class="flex justify-end">
                    <button type="button" class="nextBtn bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300">Dalej</button>
                </div>
            </fieldset>

            {{-- Krok 2: Wybór rezerwacji lub klienta --}}
            <fieldset class="wizard-step hidden" data-step="2">
                <legend class="text-xl font-semibold mb-4">2. Wybierz rezerwację lub klienta</legend>

                <div id="reservationField">
                    <label for="scheduleSelect" class="block text-gray-700 font-medium mb-1">Wybierz rezerwację</label>
                    <select id="scheduleSelect" name="schedule_id"
                            class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
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

                <div id="clientField" class="mt-4">
                    <label for="clientSelect" class="block text-gray-700 font-medium mb-1">Klient <span aria-hidden="true">*</span></label>
                    <select id="clientSelect" name="client_id" required aria-required="true"
                            class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">— Wybierz klienta —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                        <option value="SYSTEM">SYSTEM</option>
                    </select>
                    <p id="clientError" class="text-red-600 text-sm mt-1 sr-only" aria-live="polite"></p>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" class="prevBtn bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 focus:ring-2 focus:ring-gray-300">Wstecz</button>
                    <button type="button" class="nextBtn bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300">Dalej</button>
                </div>
            </fieldset>

            {{-- Krok 3: Data, godzina i czas trwania --}}
            <fieldset class="wizard-step hidden" data-step="3">
                <legend class="text-xl font-semibold mb-4">3. Data, godzina i czas trwania</legend>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="consultation_date" class="block text-gray-700 font-medium mb-1">Data konsultacji <span aria-hidden="true">*</span></label>
                        <input type="date" id="consultation_date" name="consultation_date" required
                               class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <p id="dateError" class="text-red-600 text-sm mt-1 sr-only" aria-live="polite"></p>
                    </div>
                    <div>
                        <label for="consultation_time" class="block text-gray-700 font-medium mb-1">Godzina rozpoczęcia <span aria-hidden="true">*</span></label>
                        <input type="time" id="consultation_time" name="consultation_time" required
                               class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <p id="timeError" class="text-red-600 text-sm mt-1 sr-only" aria-live="polite"></p>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="duration_hours" class="block text-gray-700 font-medium mb-1">Czas trwania (w godzinach) <span aria-hidden="true">*</span></label>
                    <input type="number" id="duration_hours" min="0.25" max="24" step="0.25" required
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                           placeholder="np. 1.5">
                    <p id="durationError" class="text-red-600 text-sm mt-1 sr-only" aria-live="polite"></p>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" class="prevBtn bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 focus:ring-2 focus:ring-gray-300">Wstecz</button>
                    <button type="button" class="nextBtn bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300">Dalej</button>
                </div>
            </fieldset>

            {{-- Krok 4: Dalsze działania i opis --}}
            <fieldset class="wizard-step hidden" data-step="4">
                <legend class="text-xl font-semibold mb-4">4. Dalsze działania i opis</legend>

                <div>
                    <label for="next_action" class="block text-gray-700 font-medium mb-1">Dalsze działania</label>
                    <input type="text" id="next_action" name="next_action"
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                           maxlength="255" placeholder="Opcjonalnie">
                </div>

                <div class="mt-4">
                    <label for="description" class="block text-gray-700 font-medium mb-1">Opis / notatka</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                              placeholder="Dodatkowe informacje..."></textarea>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" class="prevBtn bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 focus:ring-2 focus:ring-gray-300">Wstecz</button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-300">Zapisz i podpisz konsultację</button>
                </div>
            </fieldset>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const steps = Array.from(document.querySelectorAll('.wizard-step'));
            let currentStep = 0;

            const nextBtns = document.querySelectorAll('.nextBtn');
            const prevBtns = document.querySelectorAll('.prevBtn');

            const scheduleSelect = document.getElementById('scheduleSelect');
            const clientSelect = document.getElementById('clientSelect');
            const consultationDate = document.getElementById('consultation_date');
            const consultationTime = document.getElementById('consultation_time');
            const durationHours = document.getElementById('duration_hours');
            const durationMinutesHidden = document.getElementById('duration_minutes_hidden');

            const modeBtns = document.querySelectorAll('.modeBtn');

            function showStep(index){
                steps.forEach((step,i)=>step.classList.toggle('hidden', i!==index));
                steps[index].querySelector('legend').focus();
                currentStep = index;
            }

            nextBtns.forEach(btn=>btn.addEventListener('click', ()=>{
                if(validateStep(currentStep)) showStep(currentStep+1);
            }));
            prevBtns.forEach(btn=>btn.addEventListener('click', ()=>showStep(currentStep-1)));
            showStep(0);

            // Tryby konsultacji
            modeBtns.forEach(btn=>{
                btn.addEventListener('click', ()=>{
                    const mode = btn.dataset.mode;
                    modeBtns.forEach(b=>{
                        b.classList.remove('bg-blue-600','text-white');
                        b.classList.add('bg-gray-200','text-gray-800');
                        b.setAttribute('aria-pressed','false');
                    });
                    btn.classList.add('bg-blue-600','text-white');
                    btn.setAttribute('aria-pressed','true');

                    if(mode==='reservation'){
                        document.getElementById('reservationField').style.display='block';
                        clientSelect.disabled=true;
                    } else {
                        document.getElementById('reservationField').style.display='none';
                        clientSelect.disabled=false;
                    }
                });
            });

            // Auto-fill po wyborze rezerwacji
            scheduleSelect?.addEventListener('change', ()=>{
                const selected = scheduleSelect.options[scheduleSelect.selectedIndex];
                if(!selected.value) return;
                clientSelect.value = selected.dataset.client;
                consultationDate.value = selected.dataset.date;
                consultationTime.value = selected.dataset.time;
                durationHours.value = (selected.dataset.duration/60).toFixed(2);
            });

            // Konwersja godzin na minuty
            durationHours.addEventListener('input', ()=>{
                const hours=parseFloat(durationHours.value);
                durationMinutesHidden.value=!isNaN(hours)?Math.round(hours*60):'';
            });

            // Walidacja kroków
            function validateStep(step){
                let valid=true;
                if(step===1 && !clientSelect.value){
                    const err=document.getElementById('clientError');
                    err.textContent='Wybierz klienta.';
                    err.classList.remove('sr-only');
                    valid=false;
                } else document.getElementById('clientError').classList.add('sr-only');

                if(step===2){
                    const hours=parseFloat(durationHours.value);
                    if(!consultationDate.value || !consultationTime.value || !hours || hours<=0){
                        valid=false;
                        if(!consultationDate.value){
                            const dErr=document.getElementById('dateError');
                            dErr.textContent='Podaj datę konsultacji.';
                            dErr.classList.remove('sr-only');
                        } else document.getElementById('dateError').classList.add('sr-only');

                        if(!consultationTime.value){
                            const tErr=document.getElementById('timeError');
                            tErr.textContent='Podaj godzinę rozpoczęcia.';
                            tErr.classList.remove('sr-only');
                        } else document.getElementById('timeError').classList.add('sr-only');

                        if(!hours || hours<=0){
                            const durErr=document.getElementById('durationError');
                            durErr.textContent='Podaj poprawny czas trwania.';
                            durErr.classList.remove('sr-only');
                        } else document.getElementById('durationError').classList.add('sr-only');
                    }
                }

                return valid;
            }

            // Submit – zapis + automatyczny podpis
            const form=document.getElementById('consultationWizard');
            form.addEventListener('submit', async e=>{
                e.preventDefault();
                durationMinutesHidden.value=Math.round(parseFloat(durationHours.value)*60);
                const formData=new FormData(form);

                // Zapis konsultacji
                const saveRes=await fetch(form.action, {
                    method:'POST',
                    headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                    body:formData
                });
                const saveData=await saveRes.json();
                if(saveData.success){
                    // Automatyczny podpis
                    const signRes=await fetch(`/consultations/${saveData.consultation_id}/sign`, {
                        method:'POST',
                        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}
                    });
                    const signData=await signRes.json();
                    if(signData.success){
                        window.location.href="{{ route('consultations.index') }}";
                    } else alert('Błąd podpisu: '+signData.message);
                } else alert('Błąd zapisu: '+saveData.message);
            });
        });
    </script>
@endsection
