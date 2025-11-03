<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use App\Models\Client;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Lista terminarza
    public function index()
    {
        $schedules = Schedule::with('client')
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        return view('Schedule.index', compact('schedules'));
    }

    // Formularz dodawania nowego terminu
    public function create()
    {
        $clients = Client::all();
        return view('Schedule.create', compact('clients'));
    }

    // Zapisanie nowego terminu
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'time' => 'required',
            'duration_minutes' => 'required|integer|min:1',
            'status' => 'required|in:preliminary,confirmed',
            'description' => 'nullable|string|max:255',
        ], [
            'client_id.required' => 'Wybierz klienta.',
            'date.required' => 'Podaj datę konsultacji.',
            'time.required' => 'Podaj godzinę rozpoczęcia.',
            'duration_minutes.required' => 'Podaj czas trwania konsultacji w minutach.',
            'status.required' => 'Wybierz status konsultacji.',
        ]);

        $start_time = Carbon::parse($validated['date'] . ' ' . $validated['time']);

        Schedule::create([
            'user_id' => Auth::id(),
            'client_id' => $validated['client_id'],
            'start_time' => $start_time,
            'duration_minutes' => $validated['duration_minutes'],
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('schedules.index')
            ->with('success', 'Nowy termin został dodany.');
    }

    // Formularz edycji
    public function edit(Schedule $schedule)
    {
        $clients = Client::all();
        return view('Schedule.edit', compact('schedule', 'clients'));
    }

    // Aktualizacja terminu
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'time' => 'required',
            'duration_minutes' => 'required|integer|min:1',
            'status' => 'required|in:preliminary,confirmed,cancelled,no_show,cancelled_by_feer,cancelled_by_client,attended',
            'description' => 'nullable|string|max:255',
        ]);

        $schedule->update([
            'client_id' => $validated['client_id'],
            'start_time' => Carbon::parse($validated['date'] . ' ' . $validated['time']),
            'duration_minutes' => $validated['duration_minutes'],
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('schedules.index')->with('success', 'Termin został zaktualizowany.');
    }

    // Usunięcie terminu
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', 'Termin został usunięty.');
    }

    // Oznaczenie obecności
    public function markAttendance(Schedule $schedule)
    {
        $schedule->update(['status' => 'attended']);
        return redirect()->route('schedules.index')->with('success', 'Obecność została zaznaczona.');
    }

    // Odwołanie przez FEER
    public function cancelByFeer(Request $request, Schedule $schedule)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Podaj powód odwołania terminu.',
        ]);

        $schedule->update([
            'status' => 'cancelled_by_feer',
            'cancel_reason' => $request->reason,
        ]);

        return redirect()->route('schedules.index')
            ->with('success', 'Termin został odwołany przez FEER.');
    }

    // Odwołanie przez Beneficjenta
    public function cancelByClient(Request $request, Schedule $schedule)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Podaj powód odwołania.',
        ]);

        $schedule->update([
            'status' => 'cancelled_by_client',
            'cancel_reason' => $request->reason,
        ]);

        return redirect()->route('schedules.index')
            ->with('success', 'Termin został odwołany przez Beneficjenta.');
    }

    public function calendar()
    {
        $schedules = Schedule::with('client')->get();

        $events = $schedules->map(function($schedule) {
            return [
                'title' => $schedule->client->name ?? 'Brak klienta',
                'start' => $schedule->start_time,
                'end' => $schedule->start_time->copy()->addMinutes($schedule->duration_minutes),
                'status' => $schedule->status_label,
                'color' => match($schedule->status) {
                    'preliminary' => '#facc15', // żółty
                    'confirmed' => '#22c55e',   // zielony
                    'cancelled', 'cancelled_by_feer' => 'red',
                    'cancelled_by_client' => '#ef4444', // czerwony
                    'attended' => '#0d9488',    // turkusowy
                    default => '#9ca3af',       // szary
                }
            ];
        });

        return view('Schedule.calendar', compact('events'));
    }

// Formularz zmiany terminu
    public function rescheduleForm(Schedule $schedule)
    {
        $clients = \App\Models\Client::orderBy('name')->get();
        return view('Schedule.reschedule', compact('schedule', 'clients'));
    }

// Zapisanie zmian terminu
    public function updateReschedule(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'time' => 'required',
            'duration_minutes' => 'required|integer|min:1',
            'status' => 'required|in:preliminary,confirmed,cancelled,no_show,attended',
            'description' => 'nullable|string',
        ]);

        $schedule->client_id = $validated['client_id'];
        $schedule->start_time = $validated['date'] . ' ' . $validated['time'];
        $schedule->duration_minutes = $validated['duration_minutes'];
        $schedule->status = $validated['status'];
        $schedule->description = $validated['description'] ?? null;
        $schedule->save();

        return redirect()->route('schedules.index')->with('success', 'Termin został zaktualizowany.');
    }


    public function quickReserve(Request $request)
    {
        // POST – weryfikacja hasła lub zapis formularza
        if ($request->isMethod('POST')) {
            // Jeśli przesłano tylko hasło
            if ($request->has('quick_reserve_password') && !$request->has('client_id')) {
                if ($request->quick_reserve_password === env('QUICK_RESERVE_PASSWORD', 'Informatyka2025')) {
                    session(['quick_reserve_access' => true]);

                    // Logowanie otwarcia szybkiej rezerwacji
                    activity('quick_reservation')
                        ->withProperties([
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'accessed_at' => now()->toDateTimeString(),
                        ])
                        ->log('Otworzono szybka rezerwacja - hasło poprawne');

                    return redirect()->route('quickreservation');
                }
                return redirect()->back()->withErrors(['quick_reserve_password' => 'Nieprawidłowe hasło.']);
            }

            // Sprawdzenie sesji
            if (!session('quick_reserve_access')) {
                return redirect()->back()->withErrors(['quick_reserve_password' => 'Wpisz poprawne hasło, aby uzyskać dostęp.']);
            }

            // Walidacja danych formularza
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'date' => 'required|date',
                'time' => 'required',
                'duration_minutes' => 'required|integer|min:1',
                'status' => 'required|in:preliminary,confirmed',
                'description' => 'nullable|string|max:255',
            ]);

            $start_time = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['time']);

            $schedule = Schedule::create([
                'user_id' => auth()->id(),
                'client_id' => $validated['client_id'],
                'start_time' => $start_time,
                'duration_minutes' => $validated['duration_minutes'],
                'status' => $validated['status'],
                'description' => $validated['description'] ?? null,
            ]);

            // Logowanie dodania nowej szybkiej rezerwacji
            activity('quick_reservation')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'accessed_at' => now()->toDateTimeString(),
                    'client_id' => $schedule->client_id,
                    'client_name' => $schedule->client->name,
                ])
                ->performedOn($schedule)
                ->log('Dodano szybką rezerwację dla klienta');

            return redirect()->route('quickreservation')->with('success', 'Termin został dodany.');
        }

        // GET – dostęp do widoku
        if (!session('quick_reserve_access')) {
            return view('Schedule.quickreservation'); // widok tylko z polem hasła
        }

        // Logowanie wejścia na widok szybkiej rezerwacji (GET)
        activity('quick_reservation')
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'accessed_at' => now()->toDateTimeString(),
            ])
            ->log('Otworzono widok szybkiej rezerwacji');

        $upcomingSchedules = Schedule::with('client')
            ->whereBetween('start_time', [now(), now()->addDays(14)])
            ->orderBy('start_time', 'asc')
            ->get();

        $clients = Client::orderBy('name')->get();

        return view('Schedule.quickreservation', compact('upcomingSchedules', 'clients'));
    }


}
