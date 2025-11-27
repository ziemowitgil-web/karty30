@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">Cennik zajęć</h1>
            <button id="createButton" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Dodaj nowy wpis</button>
        </div>

        <!-- Tabela listy -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border" id="costTable">
                <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">ID</th>
                    <th class="border px-4 py-2">Nazwa</th>
                    <th class="border px-4 py-2">Usługa</th>
                    <th class="border px-4 py-2">Czy zajęcia</th>
                    <th class="border px-4 py-2">Obowiązuje od</th>
                    <th class="border px-4 py-2">Obowiązuje do</th>
                    <th class="border px-4 py-2">Kwota (zł)</th>
                    <th class="border px-4 py-2">Nr MPP</th>
                    <th class="border px-4 py-2">Akcje</th>
                </tr>
                </thead>
                <tbody id="costBody">
                @foreach($costs as $cost)
                    <tr id="costRow{{ $cost->id }}">
                        <td class="border px-4 py-2">{{ $cost->id }}</td>
                        <td class="border px-4 py-2">{{ $cost->name }}</td>
                        <td class="border px-4 py-2">{{ $cost->service }}</td>
                        <td class="border px-4 py-2">{{ $cost->classes_included ? 'Tak' : 'Nie' }}</td>
                        <td class="border px-4 py-2">{{ $cost->valid_from?->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2">{{ $cost->valid_to?->format('d/m/Y') }}</td>
                        <td class="border px-4 py-2">{{ number_format($cost->amount, 2, ',', '.') }}</td>
                        <td class="border px-4 py-2">{{ $cost->mpp_number }}</td>
                        <td class="border px-4 py-2">
                            <button class="editButton text-blue-500 mr-2" data-id="{{ $cost->id }}">Edytuj</button>
                            <button class="deleteButton text-red-500" data-id="{{ $cost->id }}">Usuń</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal dodawania/edycji -->
    <div id="costModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded w-full max-w-md relative">
            <span id="closeModal" class="absolute top-2 right-4 cursor-pointer text-gray-600 font-bold text-lg">&times;</span>
            <h2 class="text-xl font-bold mb-4" id="modalTitle">Dodaj wpis cennika</h2>

            <form id="costForm">
                @csrf
                <input type="hidden" id="costId">
                <div class="mb-2">
                    <label class="block mb-1 font-medium">Nazwa</label>
                    <input type="text" id="name" class="w-full border px-2 py-1 rounded">
                </div>
                <div class="mb-2">
                    <label class="block mb-1 font-medium">Usługa</label>
                    <input type="text" id="service" class="w-full border px-2 py-1 rounded">
                </div>
                <div class="mb-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="classes_included" class="mr-2"> Czy zajęcia wliczone
                    </label>
                </div>
                <div class="mb-2">
                    <label class="block mb-1 font-medium">Obowiązuje od</label>
                    <input type="date" id="valid_from" class="w-full border px-2 py-1 rounded">
                </div>
                <div class="mb-2">
                    <label class="block mb-1 font-medium">Obowiązuje do</label>
                    <input type="date" id="valid_to" class="w-full border px-2 py-1 rounded">
                </div>
                <div class="mb-2">
                    <label class="block mb-1 font-medium">Kwota (zł)</label>
                    <input type="number" step="0.01" id="amount" class="w-full border px-2 py-1 rounded">
                </div>
                <div class="mb-2">
                    <label class="block mb-1 font-medium">Nr MPP</label>
                    <input type="text" id="mpp_number" class="w-full border px-2 py-1 rounded">
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Zapisz</button>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('costModal');
            const createButton = document.getElementById('createButton');
            const closeModal = document.getElementById('closeModal');
            const costForm = document.getElementById('costForm');

            // Otwórz modal do dodawania
            createButton.addEventListener('click', () => {
                document.getElementById('modalTitle').innerText = 'Dodaj wpis cennika';
                costForm.reset();
                document.getElementById('costId').value = '';
                modal.classList.remove('hidden');
            });

            // Zamknij modal
            closeModal.addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            // Edytuj rekord
            document.querySelectorAll('.editButton').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    fetch(`/CostCalculatorEdit/${id}`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('modalTitle').innerText = 'Edytuj wpis cennika';
                            document.getElementById('costId').value = data.id;
                            document.getElementById('name').value = data.name;
                            document.getElementById('service').value = data.service;
                            document.getElementById('classes_included').checked = data.classes_included;
                            document.getElementById('valid_from').value = data.valid_from;
                            document.getElementById('valid_to').value = data.valid_to;
                            document.getElementById('amount').value = data.amount;
                            document.getElementById('mpp_number').value = data.mpp_number;
                            modal.classList.remove('hidden');
                        });
                });
            });

            // Usuń rekord
            document.querySelectorAll('.deleteButton').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    if(confirm('Na pewno usunąć?')) {
                        fetch(`/costs/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).then(() => {
                            document.getElementById(`costRow${id}`).remove();
                        });
                    }
                });
            });

            // Zapisz (dodaj/edytuj)
            costForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('costId').value;
                const url = id ? `/costs/${id}` : '/costs';
                const method = id ? 'PUT' : 'POST';

                fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: document.getElementById('name').value,
                        service: document.getElementById('service').value,
                        classes_included: document.getElementById('classes_included').checked,
                        valid_from: document.getElementById('valid_from').value,
                        valid_to: document.getElementById('valid_to').value,
                        amount: document.getElementById('amount').value,
                        mpp_number: document.getElementById('mpp_number').value
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        location.reload(); // prostsza aktualizacja tabeli
                    });
            });
        });
    </script>
@endsection
