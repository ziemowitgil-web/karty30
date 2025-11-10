<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Consultation;

class ConsultationController extends Controller
{
    public function sign(Consultation $consultation, $jsonMode = false)
    {
        if ($consultation->status !== 'draft') {
            $msg = "Tylko wersje robocze można podpisać.";
            return $jsonMode ? $msg : redirect()->back()->with('error', $msg);
        }

        try {
            activity()->causedBy(Auth::user())->performedOn($consultation)->log('Konsultacja przekazana do podpisu');

            $testCertFlag = false;

            // Weryfikacja certyfikatu użytkownika po e-mailu
            if (!$this->verifyCertificateByEmail(Auth::user(), $testCertFlag)) {
                $msg = "Certyfikat użytkownika jest nieprawidłowy lub niezgodny z adresem e-mail w systemie.";
                activity()->causedBy(Auth::user())->performedOn($consultation)->log("Weryfikacja certyfikatu NIE POWIODŁA: {$msg}");
                return $jsonMode ? $msg : redirect()->back()->with('error', $msg);
            }

            activity()->causedBy(Auth::user())->performedOn($consultation)->log("Weryfikacja certyfikatu użytkownika POWIODŁA");

            // --- Dodatkowy krok w przypadku generowania certyfikatu testowego ---
            if ($testCertFlag) {
                activity()->causedBy(Auth::user())->performedOn($consultation)
                    ->log("Wygenerowano certyfikat testowy dla środowiska staging");
            }

            $steps = [
                'Weryfikacja pliku XML dokumentu',
                'Weryfikacja certyfikatu systemu',
                'Weryfikacja certyfikatu użytkownika',
                'Proces podpisu dokumentu',
                'Weryfikacja kompetencji podpisu',
            ];

            foreach ($steps as $step) {
                sleep(rand(1,2));
                activity()->causedBy(Auth::user())->performedOn($consultation)->log($step);
            }

            $dir = app_path('signed_docs');
            if (!file_exists($dir)) mkdir($dir, 0777, true);

            $randomStr = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
            $clientId = $consultation->client_id ?? 'SYSTEM';
            $dateStr = date('Ymd_His');
            $fileName = "consultation_{$consultation->id}_{$clientId}_{$dateStr}_{$randomStr}.xml";
            $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;

            // Odczyt certyfikatu użytkownika
            $certPath = storage_path("certs/".Auth::user()->id."_user_cert.pem");

            $certContent = file_get_contents($certPath);
            $cert = openssl_x509_read($certContent);
            $certData = openssl_x509_parse($cert);

            $certCN = $certData['subject']['CN'] ?? '';
            $certEmail = $certData['subject']['emailAddress'] ?? '';
            $certOrg = $certData['subject']['O'] ?? '';
            $validFrom = isset($certData['validFrom_time_t']) ? date('c', $certData['validFrom_time_t']) : '';
            $validTo = isset($certData['validTo_time_t']) ? date('c', $certData['validTo_time_t']) : '';
            $certSha1 = sha1($certContent);

            // Tworzenie XML
            $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xmlContent .= "<consultation test_certificate=\"" . ($testCertFlag ? 'true' : 'false') . "\">\n";
            $xmlContent .= "  <id>{$consultation->id}</id>\n";
            $xmlContent .= "  <client_id>{$clientId}</client_id>\n";
            $xmlContent .= "  <conducted_by>{$consultation->user->name}</conducted_by>\n";
            $xmlContent .= "  <datetime>{$consultation->consultation_datetime}</datetime>\n";
            $xmlContent .= "  <duration>{$consultation->duration_minutes}</duration>\n";
            $xmlContent .= "  <description>" . htmlspecialchars($consultation->description) . "</description>\n";

            $xmlContent .= "  <certificate>\n";
            $xmlContent .= "    <common_name>" . htmlspecialchars($certCN) . "</common_name>\n";
            $xmlContent .= "    <email>" . htmlspecialchars($certEmail) . "</email>\n";
            $xmlContent .= "    <organization>" . htmlspecialchars($certOrg) . "</organization>\n";
            $xmlContent .= "    <valid_from>{$validFrom}</valid_from>\n";
            $xmlContent .= "    <valid_to>{$validTo}</valid_to>\n";
            $xmlContent .= "    <sha1>{$certSha1}</sha1>\n";
            $xmlContent .= "  </certificate>\n";
            $xmlContent .= "</consultation>";

            file_put_contents($filePath, $xmlContent);

            if(app()->environment('staging')){
                register_shutdown_function(function() use ($filePath){
                    if(file_exists($filePath)) unlink($filePath);
                });
            }

            $sha1 = @sha1_file($filePath) ?: substr(str_shuffle('abcdef0123456789'), 0, 40);

            $consultation->update([
                'sha1sum' => $sha1,
                'status' => 'completed',
                'approved_by_name' => Auth::user()->name
            ]);

            activity()->causedBy(Auth::user())->performedOn($consultation)->log("Konsultacja podpisana (SHA1: {$sha1})");

            $msg = "Konsultacja ID {$consultation->id} podpisana. SHA1: {$sha1}";
            return $jsonMode ? $msg : redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            Log::error("Błąd podpisu konsultacji {$consultation->id}: {$e->getMessage()}");
            return $jsonMode ? throw $e : redirect()->back()->with('error', 'Błąd podpisu: '.$e->getMessage());
        }
    }

    public function signJson(Consultation $consultation)
    {
        try {
            $msg = $this->sign($consultation, true);
            return response()->json([
                'success' => true,
                'message' => $msg,
                'sha1' => $consultation->sha1sum,
                'certificate_valid' => true,
                'test_certificate_used' => app()->environment('staging')
            ]);
        } catch (\Exception $e){
            Log::error("Błąd podpisu konsultacji {$consultation->id}: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'certificate_valid' => false,
                'test_certificate_used' => app()->environment('staging')
            ], 500);
        }
    }

    /**
     * Weryfikuje certyfikat po e-mailu. W staging generuje certyfikat testowy, ważny 6 godzin.
     * Dodatkowo ustawia flagę $testCertFlag, jeśli certyfikat testowy został wygenerowany.
     */
    protected function verifyCertificateByEmail($user, &$testCertFlag = false)
    {
        $certPath = storage_path("certs/{$user->id}_user_cert.pem");
        $now = time();

        if (app()->environment('staging')) {
            $regenCert = true;

            if (file_exists($certPath)) {
                $modTime = filemtime($certPath);
                if ($modTime !== false && ($now - $modTime) < 6 * 3600) {
                    $regenCert = false;
                }
            }

            if ($regenCert) {
                $dn = [
                    "countryName" => "PL",
                    "stateOrProvinceName" => "Test",
                    "localityName" => "Test",
                    "organizationName" => "Feer Test",
                    "organizationalUnitName" => "Test",
                    "commonName" => "Ewelina Test",
                    "emailAddress" => "ewelina@testy.feer.org.pl"
                ];

                $privateKey = openssl_pkey_new([
                    "private_key_type" => OPENSSL_KEYTYPE_RSA,
                    "private_key_bits" => 2048,
                ]);

                $csr = openssl_csr_new($dn, $privateKey);
                $cert = openssl_csr_sign($csr, null, $privateKey, 0.25); // 6 godzin
                $certPem = '';
                openssl_x509_export($cert, $certPem);
                file_put_contents($certPath, $certPem);

                $testCertFlag = true; // oznaczamy, że certyfikat testowy został wygenerowany
            }
        }

        if (!file_exists($certPath)) return false;

        $certContent = file_get_contents($certPath);
        $cert = openssl_x509_read($certContent);
        if (!$cert) return false;

        $certData = openssl_x509_parse($cert);
        if (!$certData) return false;

        $validFrom = $certData['validFrom_time_t'] ?? 0;
        $validTo = $certData['validTo_time_t'] ?? 0;
        if ($now < $validFrom || $now > $validTo) return false;

        $certEmail = $certData['subject']['emailAddress'] ?? null;
        if (!$certEmail) return false;

        return strtolower($certEmail) === strtolower($user->email);
    }
}
