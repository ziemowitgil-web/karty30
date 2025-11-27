<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /** Widok panelu certyfikatów */
    public function index()
    {
        return view('certifications.index');
    }

    /** Widok formularza generowania certyfikatu */
    public function generateView(): \Illuminate\View\View
    {
        return view('certifications.generate');
    }

    /** Generowanie certyfikatu X.509 dla użytkownika */
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

        try {
            $privateKey = openssl_pkey_new([
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
                'private_key_bits' => 2048,
            ]);

            if (!$privateKey) {
                return back()->with('error', 'Błąd podczas generowania klucza prywatnego.');
            }

            $keyPath = "{$this->path}/{$userId}_key.pem";
            openssl_pkey_export_to_file($privateKey, $keyPath, $password);

            $dnUser = array_merge($this->dn, [
                'commonName' => Auth::user()->name,
                'emailAddress' => Auth::user()->email,
            ]);

            $csr = openssl_csr_new($dnUser, $privateKey);
            $cert = openssl_csr_sign($csr, null, $privateKey, $this->validDays);

            if (!$cert) {
                return back()->with('error', 'Błąd podczas generowania certyfikatu.');
            }

            $certPath = "{$this->path}/{$userId}_cert.pem";
            openssl_x509_export_to_file($cert, $certPath);

        } catch (\Exception $e) {
            return back()->with('error', 'Wystąpił błąd: ' . $e->getMessage());
        }

        return redirect()->route('certificates.index')
            ->with('success', 'Certyfikat został wygenerowany.');
    }

    /** Widok szczegółów certyfikatu użytkownika */
    public function certificateDetailsView(int $userId)
    {
        $certificates = $this->getUserCertificate($userId);

        $certPath = $certificates['cert'] ?? null;
        $keyPath = $certificates['key'] ?? null;
        $certInfo = null;
        $error = null;

        if ($certPath && File::exists($certPath)) {
            try {
                $certContent = File::get($certPath);
                $certInfo = openssl_x509_parse($certContent);
            } catch (\Exception $e) {
                $error = 'Nie udało się odczytać certyfikatu.';
            }
        } else {
            $error = 'Certyfikat nie został jeszcze wygenerowany.';
        }

        return view('certifications.details', compact('certPath', 'keyPath', 'certInfo', 'error'));
    }

    /** Pobieranie certyfikatu lub klucza */
    public function download(int $userId, string $type): StreamedResponse
    {
        $certificates = $this->getUserCertificate($userId);
        $certPath = $certificates['cert'] ?? null;
        $keyPath = $certificates['key'] ?? null;

        $file = match($type) {
            'cert' => $certPath,
            'key' => $keyPath,
            default => null
        };

        if (!$file || !File::exists($file)) {
            abort(404, 'Plik nie istnieje.');
        }

        return response()->download($file);
    }

    /** Cofnięcie certyfikatu */
    public function revokeCertificate(int $userId)
    {
        $certificates = $this->getUserCertificate($userId);
        $certPath = $certificates['cert'] ?? null;
        $keyPath = $certificates['key'] ?? null;

        $deletedCert = $certPath ? File::delete($certPath) : false;
        $deletedKey = $keyPath ? File::delete($keyPath) : false;

        $message = ($deletedCert && $deletedKey)
            ? 'Certyfikat został cofnięty.'
            : 'Nie znaleziono certyfikatu do cofnięcia.';

        return redirect()->route('certificates.index')->with('success', $message);
    }

    /** Pobiera ścieżki certyfikatu i klucza użytkownika */
    public function getUserCertificate(int $userId): array
    {
        $certPath = "{$this->path}/{$userId}_cert.pem";
        $keyPath = "{$this->path}/{$userId}_key.pem";

        return [
            'cert' => File::exists($certPath) ? $certPath : null,
            'key' => File::exists($keyPath) ? $keyPath : null,
        ];
    }
}
