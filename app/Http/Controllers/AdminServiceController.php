<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;


class AdminServiceController extends Controller
{
    /**
     * Panel główny administratora
     *
     * @return \Illuminate\View\View
     */

    public function dashboard()
    {
        $userCount = User::count();
        $logCount = Activity::count();
        $recentLogs = Activity::latest()->take(10)->get(); // 10 ostatnich logów

        return view('AdminService.dashboard', compact('userCount', 'logCount', 'recentLogs'));
    }

    /**
     * Logi aktywności
     *
     * @return \Illuminate\View\View
     */
    public function log()
    {
        $logs = Activity::latest()->paginate(20);
        return view('AdminService.logs', compact('logs'));
    }

    /**
     * Wyczyść wszystkie logi
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearLog()
    {
        Activity::truncate();
        return redirect()->route('admin.logs')->with('success', 'Logi zostały wyczyszczone.');
    }

    /**
     * Aktualizacja zmiennej .env
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
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
     *
     * @param Request $request
     * @return \Illuminate\View\View
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
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function editUser(User $user)
    {
        return view('AdminService.UserMgmt.edit', compact('user'));
    }

    /**
     * Aktualizacja użytkownika
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
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
     *
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.list')->with('success', 'Użytkownik został usunięty.');
    }

    /**
     * Formularz dodawania nowego użytkownika
     *
     * @return \Illuminate\View\View
     */
    public function createUser()
    {
        return view('AdminService.UserMgmt.create');
    }

    /**
     * Zapis nowego użytkownika i wysyłka maila z hasłem
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'document_number' => 'nullable|string|max:255',
            'document_type' => 'nullable|string|max:255',
            'document_issuer' => 'nullable|string|max:255',
        ]);

        $password = \Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $password,
            'document_number' => $request->document_number,
            'document_type' => $request->document_type,
            'document_issuer' => $request->document_issuer,
        ]);

        // Wyślij maila z hasłem
        \Mail::to($user->email)->send(new \App\Mail\UserCreated($user, $password));

        return redirect()->route('admin.users.list')->with('success', 'Użytkownik został dodany i otrzymał maila z hasłem.');
    }

    /**
     * Generuje certyfikat X.509 serwera i zapisuje klucz prywatny i certyfikat.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateServerCertificate(Request $request)
    {
        $request->validate([
            'common_name' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'country' => 'nullable|string|size:2',
        ]);

        $dn = [
            "commonName" => $request->common_name,
            "organizationName" => $request->organization ?? 'MyOrganization',
            "countryName" => $request->country ?? 'PL',
        ];

        // Generowanie klucza prywatnego
        $privateKey = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        if (!$privateKey) {
            return back()->with('error', 'Nie udało się wygenerować klucza prywatnego.');
        }

        // Generowanie certyfikatu (ważny przez 365 dni)
        $csr = openssl_csr_new($dn, $privateKey);
        $x509 = openssl_csr_sign($csr, null, $privateKey, 365);

        if (!$x509) {
            return back()->with('error', 'Nie udało się wygenerować certyfikatu X.509.');
        }

        // Tworzenie katalogu, jeśli nie istnieje
        $certPath = storage_path('app/certs');
        if (!file_exists($certPath)) {
            mkdir($certPath, 0755, true);
        }

        $privateKeyPath = $certPath . '/server.key';
        $certPathFile = $certPath . '/server.crt';

        // Zapis klucza prywatnego
        openssl_pkey_export_to_file($privateKey, $privateKeyPath);

        // Zapis certyfikatu
        openssl_x509_export_to_file($x509, $certPathFile);

        return back()->with('success', "Certyfikat X.509 został wygenerowany.\nPlik certyfikatu: {$certPathFile}\nPlik klucza: {$privateKeyPath}");
    }



}
