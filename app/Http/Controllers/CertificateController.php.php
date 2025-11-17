<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Kontroler odpowiedzialny za obsługę certyfikatów X.509 użytkowników.
 *
 * Certyfikaty i klucze przechowywane są w katalogu określonym w
 * config/certifications.php (domyślnie /app/certifications).
 */
class CertificateController extends Controller
{
    /** @var string Ścieżka do katalogu z certyfikatami */
    protected string $path;

    /** @var array Domyślne dane DN (Distinguished Name) dla certyfikatów */
    protected array $dn;

    /** @var int Liczba dni ważności certyfikatu */
    protected int $validDays;

    public function __construct()
    {
        $this->middleware('auth');

        $this->path = config('certifications.path');
        $this->dn = config('certifications.dn');
        $this->validDays = config('certifications.valid_days');

        if (!File::exists($this->path)) {
            File::makeDirectory($this->path, 0755, true);
        }
    }

    /**
     * Widok listy certyfikatów / panel główny.
     */
    public function indexView()
    {
        return view('certificates.index');
    }

    /**
     * Widok formularza generowania certyfikatu.
     */
    public function generateView()
    {
        return view('certificates.generate');
    }

    /**
     * Generuje self-signed certyfikat X.509 użytkownika wraz z zaszyfrowanym kluczem prywatnym.
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

        // generowanie klucza prywatnego
        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);

        // eksport klucza prywatnego zaszyfrowanego hasłem
        $keyPath = "{$this->path}/{$userId}_key.pem";
        openssl_pkey_export_to_file($privateKey, $keyPath, $password);

        // DN użytkownika
        $dnUser = array_merge($this->dn, [
            'commonName' => Auth::user()->name,
            'emailAddress' => Auth::user()->email,
        ]);

        // CSR
        $csr = openssl_csr_new($dnUser, $privateKey);

        // self-signed cert
        $cert = openssl_csr_sign($csr, null, $privateKey, $this->validDays);

        // zapis certyfikatu
        $certPath = "{$this->path}/{$userId}_cert.pem";
        openssl_x509_export_to_file($cert, $certPath);

        return redirect()->route('certificates.details', ['userId' => $userId])
            ->with('success', 'Certyfikat został wygenerowany.');
    }

    /**
     * Widok szczegółów certyfikatu i klucza prywatnego użytkownika.
     */
    public function certificateDetailsView(int $userId)
    {
        ['cert' => $certPath, 'key' => $keyPath] = $this->getUserCertificate($userId);

        $certContent = File::get($certPath);
        $certInfo = openssl_x509_parse($certContent);

        return view('certificates.details', compact('certPath', 'keyPath', 'certInfo'));
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
     * Cofnięcie certyfikatu użytkownika (usunięcie certyfikatu i klucza prywatnego).
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
     * Pobiera ścieżki do certyfikatu i klucza prywatnego użytkownika.
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
