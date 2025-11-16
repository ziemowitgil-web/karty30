<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class AdminServiceController extends Controller
{
    /**
     * Panel główny administratora
     */
    public function dashboard()
    {
        $userCount = User::count();
        $logCount = Activity::count();

        return view('AdminService.dashboard', compact('userCount', 'logCount'));
    }

    /**
     * Logi aktywności
     */
    public function log()
    {
        $logs = Activity::latest()->paginate(20);
        return view('AdminService.logs', compact('logs'));
    }

    /**
     * Wyczyść wszystkie logi
     */
    public function clearLog()
    {
        Activity::truncate();
        return redirect()->route('admin.logs')->with('success', 'Logi zostały wyczyszczone.');
    }

    /**
     * Aktualizacja zmiennej .env
     */
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

        $envContents = file_get_contents($envPath);
        $value = str_contains($value, ' ') ? "\"{$value}\"" : $value;

        if (preg_match("/^{$key}=.*$/m", $envContents)) {
            $envContents = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContents);
        } else {
            $envContents .= PHP_EOL . "{$key}={$value}";
        }

        file_put_contents($envPath, $envContents);

        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');

        return back()->with('success', "Zmienna {$key} została zaktualizowana.");
    }

    /**
     * Lista użytkowników z wyszukiwaniem i paginacją
     */
    public function UserList(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('AdminService.UserMgmt.list', compact('users'));
    }

    /**
     * Formularz edycji użytkownika
     */
    public function editUser(User $user)
    {
        return view('AdminService.UserMgmt.edit', compact('user'));
    }

    /**
     * Aktualizacja użytkownika
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'document_number' => 'nullable|string|max:255',
            'document_type' => 'nullable|string|max:255',
            'document_issuer' => 'nullable|string|max:255',
        ]);

        $user->update($request->only([
            'name', 'email', 'document_number', 'document_type', 'document_issuer'
        ]));

        return redirect()->route('admin.users.list')->with('success', 'Użytkownik został zaktualizowany.');
    }

    /**
     * Usuwanie użytkownika
     */
    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.list')->with('success', 'Użytkownik został usunięty.');
    }
}
