@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4 max-w-4xl">
        <h1 class="text-3xl font-bold mb-6 text-center">Dodaj nowego klienta</h1>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('clients.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block font-medium">Imię i nazwisko <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full border px-3 py-2 rounded" required>
                </div>

                <div>
                    <label class="block font-medium">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Opcjonalny" class="w-full border px-3 py-2 rounded">
                    <small class="text-gray-500 text-sm">Jeśli nie podasz, zostanie użyty test@example.com</small>
                </div>

                <div>
                    <label class="block font-medium">Telefon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border px-3 py-2 rounded">
                </div>

                <div>
                    <label class="block font-medium">Status</label>
                    <select name="status" class="w-full border px-3 py-2 rounded">
                        <option value="">-- wybierz --</option>
                        <option value="enrolled" {{ old('status')=='enrolled' ? 'selected' : '' }}>Zapisany</option>
                        <option value="ready" {{ old('status')=='ready' ? 'selected' : '' }}>Gotowy</option>
                        <option value="to_settle" {{ old('status')=='to_settle' ? 'selected' : '' }}>Do rozliczenia</option>
                        <option value="other" {{ old('status')=='other' ? 'selected' : '' }}>Inne</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium">Preferowana metoda kontaktu</label>
                    <select name="preferred_contact_method" class="w-full border px-3 py-2 rounded">
                        <option value="">-- wybierz --</option>
                        <option value="email" {{ old('preferred_contact_method')=='email' ? 'selected' : '' }}>Email</option>
                        <option value="phone" {{ old('preferred_contact_method')=='phone' ? 'selected' : '' }}>Telefon</option>
                        <option value="sms" {{ old('preferred_contact_method')=='sms' ? 'selected' : '' }}>SMS</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium">Data urodzenia</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="w-full border px-3 py-2 rounded">
                </div>

                <div>
                    <label class="block font-medium">Płeć</label>
                    <select name="gender" class="w-full border px-3 py-2 rounded">
                        <option value="">-- wybierz --</option>
                        <option value="male" {{ old('gender')=='male' ? 'selected' : '' }}>Mężczyzna</option>
                        <option value="female" {{ old('gender')=='female' ? 'selected' : '' }}>Kobieta</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium">Adres</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="w-full border px-3 py-2 rounded">
                </div>

                <div class="md:col-span-2">
                    <label class="block font-medium">Dostępność</label>
                    <small class="text-gray-500 block mb-1">Wybierz dni tygodnia i przedziały godzinowe</small>
                    @php
                        $days = ['Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota','Niedziela'];
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($days as $day)
                            <div class="border p-2 rounded">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="available_days[{{ $loop->index }}][day]" value="{{ $day }}" class="mr-2">
                                    {{ $day }}
                                </label>
                                <input type="text" name="available_days[{{ $loop->index }}][slots]" placeholder="09:00-11:00, 14:00-16:00" class="mt-1 w-full border px-2 py-1 rounded">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center mt-2">
                        <input type="checkbox" name="consent" value="1" class="mr-2" {{ old('consent') ? 'checked' : '' }}>
                        Zgoda na przetwarzanie danych
                    </label>
                </div>

                <div class="md:col-span-2 pt-4">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 w-full">
                        Dodaj klienta
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
