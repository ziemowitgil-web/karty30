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
     * Display a listing of clients.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $clients = Client::all();

        foreach ($clients as $client) {
            // Jeśli klient ma przynajmniej jedną konsultację w systemie
            $hasSchedule = Schedule::where('client_id', $client->id)->exists();
            if ($hasSchedule) {
                $client->status = 'ready';
            }
        }

        return view('Client.index', compact('clients'));
    }

    /**
     * Show the form for creating a new client.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('Client.create');
    }

    /**
     * Store a newly created client in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Podstawowy email, jeśli nie podano
        $email = $request->email ?: 'test@example.com';

        // Walidacja podstawowych pól
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email',
        ], [
            'name.required' => 'Imię i nazwisko jest wymagane.',
            'email.email' => 'Podaj poprawny adres email.',
            'email.unique' => 'Email już istnieje w systemie.',
        ]);

        $validated['email'] = $email;

        // Pola opcjonalne
        $optionalFields = [
            'phone', 'status', 'problem', 'equipment', 'date_of_birth',
            'gender', 'address', 'notes', 'consent', 'preferred_contact_method',
            'available_days', 'time_slots', 'language', 'mobility_needs', 'emergency_contact'
        ];

        foreach ($optionalFields as $field) {
            $validated[$field] = $request->$field ?? null;
        }

        $validated['consent'] = $request->has('consent');

        // JSON dla dostępności
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

        return redirect()->route('clients.index')
            ->with('success', 'Klient został dodany w systemie TyfloKonsultacje. Jest widoczny natychmiast, natomiast w CRM będzie widoczny za kilka godzin.');
    }

    /**
     * Show the form for editing the specified client.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\View\View
     */
    public function edit(Client $client)
    {
        $client->available_days = $client->available_days ? implode(', ', json_decode($client->available_days)) : '';
        $client->time_slots = $client->time_slots ? implode(', ', json_decode($client->time_slots)) : '';

        return view('client.edit', compact('client'));
    }

    /**
     * Update the specified client in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
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

        return redirect()->route('clients.index')->with('success', 'Dane klienta zostały zaktualizowane.');
    }

    /**
     * Remove the specified client from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Client $client)
    {
        $client->delete();

        activity()
            ->performedOn($client)
            ->causedBy(auth()->user())
            ->log('Usunięto klienta');

        return redirect()->route('clients.index')->with('success', 'Klient został pomyślnie usunięty.');
    }

    // =========================================================
    // =================== PDF / Dokumenty =====================
    // =========================================================

    /**
     * Print client documents as PDF.
     *
     * @param  \App\Models\Client  $client
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

        $html = view('pdf.client', compact('client'))->render();
        $mpdf->WriteHTML($html);

        $fileName = 'Karta_' . str_replace(' ', '_', $client->name) . '.pdf';
        return $mpdf->Output($fileName, 'D'); // D = download
    }

    // =========================================================
    // =================== Szczegóły klienta ==================
    // =========================================================

    /**
     * Display detailed information for the specified client.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\View\View
     */
    public function details(Client $client)
    {
        $client->load([
            'activities.causer',  // Historia zmian
            'schedules',          // Powiązane terminy
        ]);

        // Parsowanie dostępnych dni + slotów godzinowych
        $days_slots = $client->days_slots ? json_decode($client->days_slots, true) : [];

        return view('Client.details', compact('client', 'days_slots'));
    }

    // =========================================================
    // ==================== Eksport Excel =====================
    // =========================================================

    /**
     * Export the list of clients to XLS.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportXls()
    {
        $fileName = 'lista_klientow_' . date('Y_m_d_H_i') . '.xlsx';
        return ExcelController::download(new ClientsExport, $fileName);
    }
}
