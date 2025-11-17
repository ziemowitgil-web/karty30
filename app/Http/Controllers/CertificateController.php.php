<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

/**
 * Kontroler odpowiedzialny za generowanie, zarządzanie i walidację
 * certyfikatów X.509 użytkowników.
 */
class CertificateController extends Controller
{
    protected string $path;
    protected array $dn;
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
     * Widok szczegółów certyfikatu użytkownika
     */
    public function certificateDetailsView()
    {
        $userId = Auth::id();
        $certPath = $this->path . '/' . $userId . '_user_cert.pem';
        $certExists = File::exists($certPath);
        $certData = null;

        if ($certExists) {
            $certContent = File::get($certPath);
            $certData = openssl_x509_parse($certContent);
        }

        return view('certifications.index', [
            'certExists' => $certExists,
            'certData' => $certData,
        ]);
    }

    /**
     * Generowanie self-signed certyfikatu
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

        $userId = Auth::id();
        $password = $request->key_password;

        $keyPath = $this->path . '/' . $userId . '_key.pem';
        $certPath = $this->path . '/' . $userId . '_user_cert.pem';

        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);

        openssl_pkey_export_to_file($privateKey, $keyPath, $password);

        $dnUser = array_merge($this->dn, [
            'commonName' => Auth::user()->name,
            'emailAddress' => Auth::user()->email,
        ]);

        $csr = openssl_csr_new($dnUser, $privateKey);
        $cert = openssl_csr_sign($csr, null, $privateKey, $this->validDays);
        openssl_x509_export_to_file($cert, $certPath);

        return redirect()->route('certificates.index')->with('success', 'Certyfikat wygenerowany.');
    }

    /**
     * Cofnięcie certyfikatu
     */
    public function revokeCertificate($userId)
    {
        $certPath = $this->path . '/' . $userId . '_user_cert.pem';
        $keyPath = $this->path . '/' . $userId . '_key.pem';

        if (File::exists($certPath)) File::delete($certPath);
        if (File::exists($keyPath)) File::delete($keyPath);

        return redirect()->route('certificates.index')->with('success', 'Certyfikat został cofnięty.');
    }
}
