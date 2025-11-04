<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use App\Http\Controllers\ExcelController;

class ClientController extends Controller
{
    // =========================================================
    // ===================== Klienci ===========================
    // =========================================================

    /**
     * Wyświetla listę wszystkich klientów.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $clients = Client::all();

        foreach ($clients as $client) {
            $hasSchedule = Schedule::where('client_id', $client->id)->exists();
            if ($hasSchedule) {
                $client->status = 'ready';
            }
        }

        return view('Client.index', compact('clients'));
    }

    /**
     * Wyświetla formularz tworzenia nowego klienta.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('Client.create');
    }

    /**
     * Zapisuje nowego klienta w bazie danych.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $email = $request->email ?: 'test@example.com';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email',
        ], [
            'name.required' => 'Imię i nazwisko jest wymagane.',
            'email.email' => 'Podaj poprawny adres email.',
            'email.unique' => 'Email już istnieje w systemie.',
        ]);

        $validated['email'] = $email;

        $optionalFields = [
            'phone', 'status', 'problem', 'equipment', 'date_of_birth',
            'gender', 'address', 'notes', 'consent', 'preferred_contact_method',
            'available_days', 'time_slots', 'language', 'mobility_needs', 'emergency_contact'
        ];

        foreach ($optionalFields as $field) {
            $validated[$field] = $request->$field ?? null;
        }

        $validated['consent'] = $request->has('consent');

        if ($validated['available_days']) {
            $validated['available_days'] = json_encode($validated['available_days']);
        }

        if ($validated['time_slots']) {
            $validated['time_slots'] = json_encode($validated['time_slots']);
        }

        $client = Client::create($validated);

        activity()
            ->performedOn($client)
            ->causedBy(auth()->user())
            ->log('Dodano nowego klienta');

        return redirect()->route('Clients.index')
            ->with('success', 'Klient został dodany w systemie TyfloKonsultacje. Jest widoczny natychmiast, natomiast w CRM będzie widoczny za kilka godzin.');
    }

    /**
     * Wyświetla formularz edycji danych klienta.
     *
     * @param \App\Models\Client $client
     * @return \Illuminate\View\View
     */
    public function edit(Client $client)
    {
        $client->available_days = $client->available_days ? implode(', ', json_decode($client->available_days)) : '';
        $client->time_slots = $client->time_slots ? implode(', ', json_decode($client->time_slots)) : '';

        return view('Client.edit', compact('client'));
    }

    /**
     * Aktualizuje dane klienta w bazie danych.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Client $client
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:enrolled,ready,to_settle,other',
            'problem' => 'nullable|string',
            'equipment' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'consent' => 'nullable|boolean',
            'preferred_contact_method' => 'required|in:email,phone,sms',
            'available_days' => 'nullable|string',
            'time_slots' => 'nullable|string',
        ]);

        $validated['consent'] = $request->has('consent');
        $validated['available_days'] = $validated['available_days']
            ? json_encode(array_map('trim', explode(',', $validated['available_days'])))
            : null;
        $validated['time_slots'] = $validated['time_slots']
            ? json_encode(array_map('trim', explode(',', $validated['time_slots'])))
            : null;

        $client->update($validated);

        activity()
            ->performedOn($client)
            ->causedBy(auth()->user())
            ->log('Zaktualizowano dane klienta');

        return redirect()->route('Clients.index')->with('success', 'Dane klienta zostały zaktualizowane.');
    }

    /**
     * Usuwa klienta z bazy danych.
     *
     * @param \App\Models\Client $client
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Client $client)
    {
        $client->delete();

        activity()
            ->performedOn($client)
            ->causedBy(auth()->user())
            ->log('Usunięto klienta');

        return redirect()->route('Clients.index')->with('success', 'Klient został pomyślnie usunięty.');
    }

    // =========================================================
    // =================== PDF / Dokumenty =====================
    // =========================================================

    /**
     * Generuje PDF z dokumentami klienta.
     *
     * @param \App\Models\Client $client
     * @return \Mpdf\Mpdf
     */
    public function printDocuments(Client $client)
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 20,
        ]);

        $html = view('Client.pdf', compact('client'))->render();
        $mpdf->WriteHTML($html);

        $fileName = 'Karta_' . str_replace(' ', '_', $client->name) . '.pdf';
        return $mpdf->Output($fileName, 'D');
    }

    // =========================================================
    // =================== Szczegóły klienta ==================
    // =========================================================

    /**
     * Wyświetla szczegółowe informacje o kliencie.
     *
     * @param \App\Models\Client $client
     * @return \Illuminate\View\View
     */
    public function details(Client $client)
    {
        $client->load([
            'activities.causer',
            'schedules',
        ]);

        $days_slots = $client->days_slots ? json_decode($client->days_slots, true) : [];

        return view('Client.details', compact('client', 'days_slots'));
    }

    // =========================================================
    // ==================== Eksport Excel =====================
    // =========================================================

    /**
     * Eksportuje listę klientów do pliku XLS.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportXls()
    {
        $fileName = 'lista_klientow_' . date('Y_m_d_H_i') . '.xlsx';
        return ExcelController::download(new ClientsExport, $fileName);
    }
}
