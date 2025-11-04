<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Consultation;
use App\Models\Schedule;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Redis;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // --- TRYB APLIKACJI ---
        $testMode = env('TEST_MODE', 1) == 1 ? 'TRYB TESTOWY' : 'PRODUKCJA';

        // --- STATUS REDIS ---
        $redisStatus = 'Dostępny';
        if ($testMode === 'PRODUKCJA') {
            try {
                Redis::connection()->ping();
            } catch (\Exception $e) {
                $redisStatus = "Błąd połączenia z Redis! Kolejkowanie może nie działać.";
            }
        } else {
            $redisStatus = 'Błąd połączenia - blokada. Tryb testowy REDIS nie zbiera danych sesji. ';
        }

        // --- LOG DO ACTIVITY ---
        activity()
            ->causedBy($user)
            ->withProperties([
                'redis_status' => $redisStatus,
                'app_mode' => $testMode,
            ])
            ->log('Wejście na stronę główną');

        // --- STATYSTYKI KONSULTACJI ---
        $rawStats = Consultation::selectRaw('status, COUNT(*) as total')
            ->where('user_id', $user->id)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $stats = [
            'draft' => $rawStats['draft'] ?? 0,
            'completed' => $rawStats['completed'] ?? 0,
            'cancelled' => $rawStats['cancelled'] ?? 0,
        ];

        // --- OSTATNIE AKCJE ---
        $recentActions = Activity::where('causer_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function($activity) {
                return (object) [
                    'created_at' => $activity->created_at,
                    'action_type' => $activity->description,
                    'target_name' => $activity->subject?->name ?? ($activity->subject_type ?? '-'),
                    'status_label' => $activity->properties['status'] ?? '-',
                ];
            });

        // --- HARMONOGRAM ---
        $todaySchedules = Schedule::whereDate('start_time', today())
            ->where('user_id', $user->id)
            ->with('client')
            ->orderBy('start_time')
            ->get();

        $weekSchedules = Schedule::whereBetween('start_time', [today()->addDay(), today()->addDays(7)])
            ->where('user_id', $user->id)
            ->with('client')
            ->orderBy('start_time')
            ->get();

        // --- TRYB DOSTĘPNOŚCI ---
        if ($request->has('accessible')) {
            if ($request->boolean('accessible')) {
                session(['accessible_view' => true]);
            } else {
                session()->forget('accessible_view');
            }
        }

        $accessible = session('accessible_view', false);
        $view = $accessible ? 'home2' : 'home';
        $hasWebAuthnKeys = $user->hasWebauthnKey();

        // --- PRZEKAZANIE STATUSU REDIS DO WIDOKU ---
        return view($view, compact(
            'user',
            'stats',
            'recentActions',
            'todaySchedules',
            'weekSchedules',
            'hasWebAuthnKeys',
            'redisStatus',
            'testMode'
        ));
    }

    public function toggleAccessible(Request $request)
    {
        $accessible = session('accessible_view', false);
        session(['accessible_view' => !$accessible]);

        return response()->json([
            'status' => 'success',
            'accessible' => !$accessible
        ]);
    }
}
