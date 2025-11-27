<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Kontroler obsługujący certyfikaty X.509 użytkowników.
 */
class CertificateController extends Controller
{
    protected string $path;
    protected array $dn;
    protected int $validDays;

    public function __construct()
    {
        $this->middleware('auth');

        $this->path = config('certifications.path', storage_path('app/certifications'));
        $this->dn = config('certifications.dn', []);
        $this->validDays = config('certifications.valid_days', 365);

        if (!File::exists($this->path)) {
            File::makeDirectory($this->path, 0755, true);
        }
    }

    /**
     * Widok panelu certyfikatów.
     */
    public function index()
    {
        public function indexView()
    {
        try {
            return view('certifications.index');
        } catch (\Throwable $e) {
            dd('Błąd ładowania widoku: ' . $e->getMessage(), $e->getTrace());
        }
    }
        }


    /**
     * Widok formularza generowania certyfikatu.
     */
    public function generateView(): \Illuminate\View\View
    {
        return view('certifications.generate');
    }

    /**
     * Generuje certyfikat X.509 dla użytkownika.
     */
    public function generateCertificate(Request $request, int $userId)
    {
        $request->validate([
            'key_password' => [
                'required',
                'string',
                'min:6',
                function ($attr, $value, $fail) {
                    if (Hash::check($value, Auth::user()->password)) {
                        $fail('Hasło do certyfikatu nie może być takie samo jak hasło konta.');
                    }
                }
            ],
        ]);

        $password = $request->key_password;

        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);

        $keyPath = "{$this->path}/{$userId}_key.pem";
        openssl_pkey_export_to_file($privateKey, $keyPath, $password);

        $dnUser = array_merge($this->dn, [
            'commonName' => Auth::user()->name,
            'emailAddress' => Auth::user()->email,
        ]);

        $csr = openssl_csr_new($dnUser, $privateKey);
        $cert = openssl_csr_sign($csr, null, $privateKey, $this->validDays);

        $certPath = "{$this->path}/{$userId}_cert.pem";
        openssl_x509_export_to_file($cert, $certPath);

        return redirect()->route('certificates.details', ['userId' => $userId])
            ->with('success', 'Certyfikat został wygenerowany.');
    }

    /**
     * Widok szczegółów certyfikatu użytkownika.
     */
    public function getUserCertificate(int $userId): array
    {
        $certPath = "{$this->path}/{$userId}_cert.pem";
        $keyPath = "{$this->path}/{$userId}_key.pem";

        return [
            'cert' => File::exists($certPath) ? $certPath : null,
            'key' => File::exists($keyPath) ? $keyPath : null,
        ];
    }

    /**
     * Pobiera certyfikat lub klucz prywatny użytkownika.
     */
    public function download(int $userId, string $type): StreamedResponse
    {
        ['cert' => $certPath, 'key' => $keyPath] = $this->getUserCertificate($userId);

        $file = match($type) {
            'cert' => $certPath,
            'key' => $keyPath,
            default => null
        };

        if (!$file) abort(404, 'Niepoprawny typ pliku.');

        return response()->download($file);
    }

    /**
     * Cofnięcie certyfikatu użytkownika.
     */
    public function revokeCertificate(int $userId)
    {
        ['cert' => $certPath, 'key' => $keyPath] = $this->getUserCertificate($userId);

        $deletedCert = File::delete($certPath);
        $deletedKey = File::delete($keyPath);

        return redirect()->route('certificates.index')
            ->with('success', $deletedCert && $deletedKey ? 'Certyfikat został cofnięty.' : 'Błąd przy cofaniu certyfikatu.');
    }

    /**
     * Pobiera ścieżki certyfikatu i klucza użytkownika.
     */
    public function getUserCertificate(int $userId): array
    {
        $certPath = "{$this->path}/{$userId}_cert.pem";
        $keyPath = "{$this->path}/{$userId}_key.pem";

        if (!File::exists($certPath) || !File::exists($keyPath)) {
            abort(404, 'Certyfikat lub klucz prywatny nie istnieje.');
        }

        return [
            'cert' => $certPath,
            'key' => $keyPath,
        ];
    }
}
