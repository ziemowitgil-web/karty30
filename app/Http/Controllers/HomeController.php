<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Consultation;
use App\Models\Schedule;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\File;

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
            $redisStatus = 'Tryb testowy REDIS - brak danych sesji.';
        }

        // --- LOG DO ACTIVITY ---
        activity()->causedBy($user)->withProperties([
            'redis_status' => $redisStatus,
            'app_mode' => $testMode,
        ])->log('Wejście na stronę główną');

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

        // --- STATUS CERTYFIKATU ---
        $certPath = config('certifications.path') . "/{$user->id}_cert.pem";
        $certExists = File::exists($certPath);
        $certCN = 'Brak certyfikatu';
        $certOrg = null;
        $certValidUntil = null;
        $certExpiringSoon = false;
        $certStatus = 'Brak';

        if ($certExists) {
            $certContent = File::get($certPath);
            $certInfo = openssl_x509_parse($certContent);
            $certCN = $certInfo['subject']['CN'] ?? 'Nieznany użytkownik';
            $certOrg = $certInfo['subject']['O'] ?? 'Brak danych o organizacji';
            if (isset($certInfo['validTo_time_t'])) {
                $certValidUntil = date('d.m.Y', $certInfo['validTo_time_t']);
                $daysLeft = ($certInfo['validTo_time_t'] - time()) / 86400;
                $certExpiringSoon = $daysLeft <= 10;
                $certStatus = $daysLeft > 0 ? 'Aktywny' : 'Wygasł';
            }
        }

        $accessible = session('accessible_view', false);
        $view = $accessible ? 'home2' : 'home';
        $hasWebAuthnKeys = $user->hasWebauthnKey();

        return view($view, compact(
            'user',
            'stats',
            'todaySchedules',
            'weekSchedules',
            'certExists',
            'certCN',
            'certOrg',
            'certValidUntil',
            'certStatus',
            'certExpiringSoon',
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
