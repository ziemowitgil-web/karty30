<?php

namespace App\Http\Controllers;

use App\Models\AdminService;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;



class AdminServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function log()
    {
        // Pobierz logi, najnowsze pierwsze, 20 na stronę
        $logs = Activity::latest()->paginate(20);

        return view('AdminService.logs', compact('logs'));
    }

    public function clearLog(Request $request)
    {
        // Opcjonalnie: można sprawdzić rolę użytkownika
        // if (!$request->user()->isAdmin()) {
        //     abort(403);
        // }

        Activity::truncate(); // usuwa wszystkie logi

        return redirect()->route('logs')->with('success', 'Logi zostały wyczyszczone.');
    }

    public function updateEnv(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable|string',
        ]);

        $key = $request->key;
        $value = $request->value;

        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return back()->with('error', '.env file not found!');
        }

        // Odczyt pliku
        $envContents = file_get_contents($envPath);

        // Wartość z cudzysłowami jeśli zawiera spacje lub specjalne znaki
        $value = str_contains($value, ' ') ? '"' . $value . '"' : $value;

        // Jeśli zmienna istnieje -> nadpisz, jeśli nie -> dodaj
        if (preg_match("/^{$key}=.*$/m", $envContents)) {
            $envContents = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContents);
        } else {
            $envContents .= PHP_EOL . "{$key}={$value}";
        }

        file_put_contents($envPath, $envContents);

        // Opcjonalnie odśwież cache config
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');

        return back()->with('success', "Zmienna {$key} została zaktualizowana.");
    }
}

