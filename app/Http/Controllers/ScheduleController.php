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

    // ================= LISTA TERMINARZA =================
    public function index()
    {
        $schedules = Schedule::with('client')
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        return view('Schedule.index', compact('schedules'));
    }

    // ================= FORMULARZ DODAWANIA =================
    public function create()
    {
        $clients = $this->getClients();
        return view('Schedule.create', compact('clients'));
    }

    // ================= ZAPIS NOWEGO TERMINU =================
    public function store(Request $request)
    {
        $data = $this->validateSchedule($request);

        $this->createSchedule($data);

        return redirect()->route('schedules.index')
            ->with('success', 'Nowy termin został dodany.');
    }

    // ================= FORMULARZ EDYCJI =================
    public function edit(Schedule $schedule)
    {
        $clients = $this->getClients();
        return view('Schedule.edit', compact('schedule', 'clients'));
    }

    // ================= AKTUALIZACJA TERMINU =================
    public function update(Request $request, Schedule $schedule)
    {
        $data = $this->validateSchedule($request, $update = true);

        $schedule->update($data);

        return redirect()->route('schedules.index')->with('success', 'Termin został zaktualizowany.');
    }

    // ================= USUNIĘCIE TERMINU =================
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', 'Termin został usunięty.');
    }

    // ================= OZNAZENIE OBECNOŚCI =================
    public function markAttendance(Schedule $schedule)
    {
        $schedule->update(['status' => 'attended']);
        return redirect()->route('schedules.index')->with('success', 'Obecność została zaznaczona.');
    }

    // ================= ODWOŁANIE TERMINU =================
    public function cancelByFeer(Request $request, Schedule $schedule)
    {
        $data = $request->validate(['reason' => 'required|string|max:255']);
        $schedule->update(['status' => 'cancelled_by_feer', 'cancel_reason' => $data['reason']]);
        return redirect()->route('schedules.index')->with('success', 'Termin został odwołany przez FEER.');
    }

    public function cancelByClient(Request $request, Schedule $schedule)
    {
        $data = $request->validate(['reason' => 'required|string|max:255']);
        $schedule->update(['status' => 'cancelled_by_client', 'cancel_reason' => $data['reason']]);
        return redirect()->route('schedules.index')->with('success', 'Termin został odwołany przez klienta.');
    }

    // ================= KALENDARZ =================
    public function calendar()
    {
        $schedules = Schedule::with('client')->get();

        $events = $schedules->map(fn($s) => [
            'title' => $s->client->name ?? 'Brak klienta',
            'start' => $s->start_time,
            'end' => $s->start_time->copy()->addMinutes($s->duration_minutes),
            'status' => $s->status_label,
            'color' => $this->getStatusColor($s->status),
        ]);

        return view('Schedule.calendar', compact('events'));
    }

    // ================= ZMIANA TERMINU =================
    public function rescheduleForm(Schedule $schedule)
    {
        $clients = $this->getClients();
        return view('Schedule.reschedule', compact('schedule', 'clients'));
    }

    public function updateReschedule(Request $request, Schedule $schedule)
    {
        $data = $this->validateSchedule($request, $update = true);
        $schedule->update($data);

        return redirect()->route('schedules.index')->with('success', 'Termin został zaktualizowany.');
    }

    // ================= SZYBKA REZERWACJA =================
    public function quickReserve(Request $request)
    {
        if ($request->isMethod('POST')) {
            return $this->handleQuickReservePost($request);
        }

        if (!session('quick_reserve_access')) {
            return view('Schedule.quickreservation'); // tylko pole hasła
        }

        activity('quick_reservation')
            ->withProperties($this->quickReserveLogProps($request))
            ->log('Otworzono widok szybkiej rezerwacji');

        $upcomingSchedules = Schedule::with('client')
            ->whereBetween('start_time', [now(), now()->addDays(14)])
            ->orderBy('start_time')
            ->get();

        $clients = $this->getClients();
        return view('Schedule.quickreservation', compact('upcomingSchedules', 'clients'));
    }

    // ================= METODY POMOCNICZE =================
    protected function getClients()
    {
        return Client::orderBy('name')->get();
    }

    protected function validateSchedule(Request $request, $update = false)
    {
        $rules = [
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'time' => 'required',
            'duration_minutes' => 'required|integer|min:1',
            'status' => 'required|in:preliminary,confirmed,cancelled,no_show,attended,cancelled_by_feer,cancelled_by_client',
            'description' => 'nullable|string|max:255',
        ];

        $validated = $request->validate($rules);

        $validated['start_time'] = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        unset($validated['date'], $validated['time']);

        if (!$update) {
            $validated['user_id'] = Auth::id();
        }

        return $validated;
    }

    protected function createSchedule(array $data)
    {
        return Schedule::create($data);
    }

    protected function getStatusColor(string $status)
    {
        return match($status) {
            'preliminary' => '#facc15',
            'confirmed' => '#22c55e',
            'cancelled', 'cancelled_by_feer' => '#ef4444',
            'cancelled_by_client' => '#ef4444',
            'attended' => '#0d9488',
            default => '#9ca3af',
        };
    }

    protected function handleQuickReservePost(Request $request)
    {
        // Hasło tylko
        if ($request->has('quick_reserve_password') && !$request->has('client_id')) {
            if ($request->quick_reserve_password === env('QUICK_RESERVE_PASSWORD', 'Informatyka2025')) {
                session(['quick_reserve_access' => true]);
                activity('quick_reservation')
                    ->withProperties($this->quickReserveLogProps($request))
                    ->log('Otworzono szybką rezerwację - hasło poprawne');
                return redirect()->route('quickreservation');
            }
            return redirect()->back()->withErrors(['quick_reserve_password' => 'Nieprawidłowe hasło.']);
        }

        if (!session('quick_reserve_access')) {
            return redirect()->back()->withErrors(['quick_reserve_password' => 'Wpisz poprawne hasło, aby uzyskać dostęp.']);
        }

        $data = $this->validateSchedule($request);
        $schedule = $this->createSchedule($data);

        activity('quick_reservation')
            ->withProperties(array_merge($this->quickReserveLogProps($request), [
                'client_id' => $schedule->client_id,
                'client_name' => $schedule->client->name,
            ]))
            ->performedOn($schedule)
            ->log('Dodano szybką rezerwację dla klienta');

        return redirect()->route('quickreservation')->with('success', 'Termin został dodany.');
    }

    protected function quickReserveLogProps(Request $request)
    {
        return [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accessed_at' => now()->toDateTimeString(),
        ];
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'start_time' => 'required|date',
            'duration_minutes' => 'required|integer|min:15',
        ]);

        $clientId = $request->client_id;
        $startTime = $request->start_time;
        $duration = $request->duration_minutes;

        // logika sprawdzania dostępności (przykład)
        $overlapping = Schedule::where('client_id', $clientId)
            ->where('status', 'confirmed')
            ->where(function($q) use ($startTime, $duration) {
                $q->whereBetween('start_time', [$startTime, date('Y-m-d H:i:s', strtotime($startTime.' +'.$duration.' minutes'))])
                    ->orWhereBetween(DB::raw("DATE_ADD(start_time, INTERVAL duration_minutes MINUTE)"), [$startTime, date('Y-m-d H:i:s', strtotime($startTime.' +'.$duration.' minutes'))]);
            })
            ->exists();

        return response()->json([
            'available' => !$overlapping
        ]);
    }

}
