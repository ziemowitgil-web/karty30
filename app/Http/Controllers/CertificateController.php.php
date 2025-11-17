<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

/**
 * Kontroler odpowiedzialny za generowanie, pobieranie, cofanie
 * i wyświetlanie certyfikatów X.509 użytkowników.
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

    /**
     * Konstruktor kontrolera.
     * Inicjalizuje ścieżkę, domyślny DN i dni ważności z konfiguracji.
     * Tworzy katalog certyfikatów jeśli nie istnieje.
     */
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
     * Widok listy certyfikatów użytkownika.
     *
     * @return \Illuminate\View\View
     */
    public function indexView()
    {
        return view('certificates.index');
    }

    /**
     * Widok formularza generowania certyfikatu.
     *
     * @return \Illuminate\View\View
     */
    public function generateView()
    {
        return view('certificates.generate');
    }

    /**
     * Generuje self-signed certyfikat X.509 użytkownika wraz z zaszyfrowanym kluczem prywatnym.
     * Wymaga podania hasła do certyfikatu różnego od hasła konta.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function generateCertificate(Request $request)
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
        $userId = Auth::id();

        $keyPath = "{$this->path}/{$userId}_key.pem";
        $certPath = "{$this->path}/{$userId}_cert.pem";

        // generowanie klucza prywatnego
        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);

        // eksport klucza prywatnego zaszyfrowanego hasłem
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
        openssl_x509_export_to_file($cert, $certPath);

        return redirect()->route('certificates.details', $userId)
            ->with('success', 'Certyfikat wygenerowany.');
    }

    /**
     * Widok szczegółów certyfikatu i klucza prywatnego użytkownika.
     *
     * @param int $userId
     * @return \Illuminate\View\View
     */
    public function certificateDetailsView(int $userId)
    {
        $certPath = "{$this->path}/{$userId}_cert.pem";
        $keyPath = "{$this->path}/{$userId}_key.pem";

        if (!File::exists($certPath) || !File::exists($keyPath)) {
            abort(404, 'Certyfikat nie istnieje.');
        }

        return view('certificates.details', [
            'certPath' => $certPath,
            'keyPath' => $keyPath,
            'userId' => $userId,
        ]);
    }

    /**
     * Pobranie certyfikatu lub klucza prywatnego użytkownika.
     *
     * @param int $userId
     * @param string $type "cert" lub "key"
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(int $userId, string $type)
    {
        $file = match($type) {
            'cert' => "{$this->path}/{$userId}_cert.pem",
            'key' => "{$this->path}/{$userId}_key.pem",
            default => null,
        };

        if (!$file || !File::exists($file)) {
            abort(404);
        }

        return response()->download($file);
    }

    /**
     * Cofnięcie certyfikatu użytkownika (usunięcie certyfikatu i klucza prywatnego).
     *
     * @param int $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revokeCertificate(int $userId)
    {
        $certPath = "{$this->path}/{$userId}_cert.pem";
        $keyPath = "{$this->path}/{$userId}_key.pem";

        if (File::exists($certPath)) File::delete($certPath);
        if (File::exists($keyPath)) File::delete($keyPath);

        return redirect()->route('certificates.index')
            ->with('success', 'Certyfikat został cofnięty.');
    }
}
