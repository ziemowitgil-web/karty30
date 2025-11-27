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

    public function index()
    {
        return view('certifications.index');
    }

    public function generateView(): \Illuminate\View\View
    {
        return view('certifications.generate');
    }

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

        return redirect()->route('certificates.index')
            ->with('success', 'Certyfikat został wygenerowany.');
    }

    public function certificateDetailsView(int $userId)
    {
        $certificates = $this->getUserCertificate($userId);
        $certPath = $certificates['cert'];
        $keyPath = $certificates['key'];

        if (!$certPath || !$keyPath) {
            return view('certifications.details')
                ->with('error', 'Certyfikat nie został jeszcze wygenerowany.')
                ->with('certPath', null)
                ->with('keyPath', null)
                ->with('certInfo', null);
        }

        $certContent = File::get($certPath);
        $certInfo = openssl_x509_parse($certContent);

        return view('certifications.details', compact('certPath', 'keyPath', 'certInfo'));
    }

    public function download(int $userId, string $type): StreamedResponse
    {
        $certificates = $this->getUserCertificate($userId);
        $certPath = $certificates['cert'];
        $keyPath = $certificates['key'];

        $file = match($type) {
            'cert' => $certPath,
            'key' => $keyPath,
            default => null
        };

        if (!$file) abort(404, 'Niepoprawny typ pliku lub plik nie istnieje.');

        return response()->download($file);
    }

    public function revokeCertificate(int $userId)
    {
        $certificates = $this->getUserCertificate($userId);
        $certPath = $certificates['cert'];
        $keyPath = $certificates['key'];

        $deletedCert = $certPath ? File::delete($certPath) : false;
        $deletedKey = $keyPath ? File::delete($keyPath) : false;

        return redirect()->route('certificates.index')
            ->with('success', $deletedCert && $deletedKey ? 'Certyfikat został cofnięty.' : 'Błąd przy cofaniu certyfikatu.');
    }

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
