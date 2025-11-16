<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class CertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Pobranie certyfikatu użytkownika
     */
    public function getUserCertificate(int $userId): ?string
    {
        $path = app_path("certificates/{$userId}_user_cert.pem");
        return file_exists($path) ? file_get_contents($path) : null;
    }

    /**
     * Pobranie certyfikatu serwera
     */
    public function getServerCertificate(): ?string
    {
        $path = storage_path("app/server_cert.pem");
        return file_exists($path) ? file_get_contents($path) : null;
    }

    /**
     * Pobranie konfiguracji certyfikatu serwera z /config/certificate.php
     */
    public function getServerCertificateConfig(): array
    {
        return Config::get('certificate.server', []);
    }

    /**
     * Walidacja certyfikatu użytkownika
     */
    public function validateUserCertificate(string $certData, string $userEmail): void
    {
        $parsed = openssl_x509_parse($certData);
        if (!$parsed) {
            throw new \Exception("Nieprawidłowy certyfikat użytkownika.");
        }

        // Sprawdzenie email w certyfikacie
        if (!isset($parsed['subject']['emailAddress']) || $parsed['subject']['emailAddress'] !== $userEmail) {
            throw new \Exception("Certyfikat użytkownika nie pasuje do adresu e-mail.");
        }

        // Sprawdzenie ważności certyfikatu
        if (time() > $parsed['validTo_time_t']) {
            throw new \Exception("Certyfikat użytkownika wygasł.");
        }
    }

    /**
     * Widok szczegółów certyfikatu
     */
    public function certificateDetailsView(int $userId)
    {
        $userCert = $this->getUserCertificate($userId);
        $serverCert = $this->getServerCertificate();

        $userCertParsed = $userCert ? openssl_x509_parse($userCert) : null;
        $serverCertParsed = $serverCert ? openssl_x509_parse($serverCert) : null;

        return view('certificates.details', [
            'userCert' => $userCert,
            'userCertParsed' => $userCertParsed,
            'serverCert' => $serverCert,
            'serverCertParsed' => $serverCertParsed,
        ]);
    }

    /**
     * Generowanie certyfikatu użytkownika (szablon)
     * Tutaj w przyszłości możesz podłączyć OpenSSL lub API CA
     */
    public function generateCertificate(int $userId, array $userData): string
    {
        // $userData np. ['CN' => 'Jan Kowalski', 'emailAddress' => 'jan@domena.pl']
        // W tym miejscu logika generowania certyfikatu do pliku:
        $certContent = "-----BEGIN CERTIFICATE-----\nFAKECERTDATAFORUSER\n-----END CERTIFICATE-----";
        $path = app_path("certificates/{$userId}_user_cert.pem");
        file_put_contents($path, $certContent);
        return $certContent;
    }

    /**
     * Cofanie certyfikatu użytkownika
     */
    public function revokeCertificate(int $userId): bool
    {
        $path = app_path("certificates/{$userId}_user_cert.pem");
        if (file_exists($path)) {
            unlink($path);
            return true;
        }
        return false;
    }
}
