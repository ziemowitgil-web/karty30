<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AdminServiceController extends Controller
{
    /**
     * Panel główny administratora
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

    /**
     * Formularz dodawania nowego użytkownika
     */
    public function createUser()
    {
        return view('AdminService.UserMgmt.create');
    }

    /**
     * Zapis nowego użytkownika i wysyłka maila z hasłem
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

        $password = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $password,
            'document_number' => $request->document_number,
            'document_type' => $request->document_type,
            'document_issuer' => $request->document_issuer,
        ]);

        // Wyślij maila z hasłem
        Mail::to($user->email)->send(new \App\Mail\UserCreated($user, $password));

        return redirect()->route('admin.users.list')->with('success', 'Użytkownik został dodany i otrzymał maila z hasłem.');
    }

    /**
     * Wyświetla formularz do generowania certyfikatu X.509 serwera
     */
    public function showCertificateForm()
    {
        $certDir = storage_path('certificates');
        $certPath = $certDir . '/server.crt';
        $privateKeyPath = $certDir . '/server.key';

        $certificateExists = file_exists($certPath);
        $privateKeyExists = file_exists($privateKeyPath);
        $certificateInfo = $certificateExists ? openssl_x509_parse(file_get_contents($certPath)) : null;

        return view('AdminService.certificate.create', compact(
            'certificateExists',
            'privateKeyExists',
            'certificateInfo',
            'certPath',
            'privateKeyPath'
        ));
    }

    /**
     * Generuje certyfikat X.509 serwera
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

        $privateKey = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        if (!$privateKey) {
            return back()->with('error', 'Nie udało się wygenerować klucza prywatnego.');
        }

        $csr = openssl_csr_new($dn, $privateKey);
        $x509 = openssl_csr_sign($csr, null, $privateKey, 365);

        if (!$x509) {
            return back()->with('error', 'Nie udało się wygenerować certyfikatu X.509.');
        }

        // Tworzenie katalogu, jeśli nie istnieje
        $certDir = storage_path('certificates');
        if (!file_exists($certDir)) {
            mkdir($certDir, 0755, true);
        }

        $privateKeyPath = $certDir . '/server.key';
        $certPath = $certDir . '/server.crt';

        openssl_pkey_export_to_file($privateKey, $privateKeyPath);
        openssl_x509_export_to_file($x509, $certPath);

        return back()->with('success', "Certyfikat X.509 został wygenerowany.\nPlik certyfikatu: {$certPath}\nPlik klucza: {$privateKeyPath}");
    }
}
