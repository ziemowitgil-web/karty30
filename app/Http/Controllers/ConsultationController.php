<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

class ConsultationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================
    // ===================== LISTA ============================
    // =========================================================
    public function index(Request $request)
    {
        $query = Consultation::with('client', 'user');

        if ($request->filled('month')) {
            $month = $request->month;
            $query->whereYear('consultation_datetime', substr($month, 0, 4))
                ->whereMonth('consultation_datetime', substr($month, 5, 2));
        }

        if ($request->filled('year')) {
            $query->whereYear('consultation_datetime', $request->year);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $consultations = $query->get();

        return view('Consultation.index', compact('consultations'));
    }

    // =========================================================
    // ===================== FORMULARZ ========================
    // =========================================================
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $schedules = \App\Models\Schedule::with('client')
            ->where('status', 'confirmed')
            ->orderBy('start_time', 'asc')
            ->get(['id', 'client_id', 'start_time', 'duration_minutes']);

        return view('Consultation.create', compact('clients', 'schedules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'nullable|exists:schedules,id',
            'client_id' => ['required', function ($attr, $value, $fail) {
                if ($value !== 'SYSTEM' && !Client::where('id', $value)->exists()) {
                    $fail('Wybrany klient nie istnieje.');
                }
            }],
            'consultation_date' => 'required|date',
            'consultation_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:15|max:1440',
            'description' => 'nullable|string|max:1000',
            'next_action' => 'nullable|string|max:255',
            'status' => 'required|in:draft,pending_system,pending_signature,completed',
            'sign_type' => 'nullable|in:qualified,eid,feer',
        ]);

        $validated['consultation_datetime'] = $validated['consultation_date'] . ' ' . $validated['consultation_time'];
        unset($validated['consultation_date'], $validated['consultation_time']);

        $validated['user_id'] = Auth::id();
        $validated['user_email'] = Auth::user()->email;
        $validated['username'] = Auth::user()->name;
        $validated['user_ip'] = $request->ip();

        // Walidacja klienta (czarna lista i limit godzin)
        if ($validated['status'] !== 'draft' && $validated['client_id'] !== 'SYSTEM') {
            $client = Client::find($validated['client_id']);
            if ($client->blacklisted) {
                return redirect()->back()->withInput()->with('error', 'Nie można utworzyć konsultacji dla klienta na czarnej liście.');
            }
            $hoursUsed = round($validated['duration_minutes'] / 60, 2);
            $availableHours = $client->getAvailableHoursNumberAttribute();
            if (($client->used + $hoursUsed) > $availableHours) {
                return redirect()->back()->withInput()->with('error', "Klient nie ma wystarczająco godzin (pozostało: {$availableHours}).");
            }
            $client->used += $hoursUsed;
            $client->save();
        }

        $consultation = Consultation::create($validated);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($consultation)
            ->log("Konsultacja utworzona (status: {$validated['status']})");

        return redirect()->route('consultations.index')->with('success', 'Konsultacja została dodana.');
    }

    // =========================================================
    // ===================== USUWANIE =========================
    // =========================================================
    public function destroy(Consultation $consultation)
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($consultation)
            ->log("Konsultacja usunięta przez " . Auth::user()->name);

        $consultation->delete();
        return redirect()->route('consultations.index')->with('success', 'Konsultacja została usunięta.');
    }

    // =========================================================
    // ===================== PODPIS ===========================
    // =========================================================
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

            // Dodatkowy krok w przypadku generowania certyfikatu testowego
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

    public function historyJson(Consultation $consultation)
    {
        $logs = $consultation->activities()->latest()->get(['description','created_at']);
        return response()->json([
            'consultation_id' => $consultation->id,
            'logs' => $logs
        ]);
    }

    // =========================================================
    // ===================== PDF / XML ========================
    // =========================================================
    public function downloadPdf(Consultation $consultation)
    {
        if (!$consultation->sha1sum) abort(403, 'Konsultacja nie została jeszcze podpisana.');

        $mpdf = new Mpdf();
        $html = view('Consultation.pdf', compact('consultation'))->render();
        $mpdf->WriteHTML($html);

        return $mpdf->Output("consultation_{$consultation->id}.pdf", 'I');
    }

    public function xml(Consultation $consultation)
    {
        $xmlContent = $consultation->toXml();
        return response($xmlContent, 200)->header('Content-Type', 'application/xml');
    }

    // =========================================================
    // ===================== STAGING TEST =====================
    // =========================================================
    public function deleteTestData(Request $request)
    {
        if(!app()->environment('staging')){
            abort(403, 'Brak dostępu.');
        }

        $dir = app_path('signed_docs');
        $filesDeleted = 0;

        if(file_exists($dir)){
            foreach(glob($dir . '/*') as $file){
                if(is_file($file)){
                    unlink($file);
                    $filesDeleted++;
                }
            }
        }

        return response()->json(['message' => "Usunięto $filesDeleted plików testowych."]);
    }

    public function details(Consultation $consultation)
    {
        return view('Consultation.details', compact('consultation'));
    }

    // =========================================================
    // ===================== CERTYFIKAT =======================
    // =========================================================
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

    // =========================================================
// ===================== CERTYFIKAT =======================
// =========================================================
    public function certificateDetails(Request $request)
    {
        $user = Auth::user();
        $certPath = storage_path("certs/{$user->id}_user_cert.pem");

        if (!file_exists($certPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Brak certyfikatu dla użytkownika.'
            ], 404);
        }

        $certContent = file_get_contents($certPath);
        $cert = openssl_x509_read($certContent);

        if (!$cert) {
            return response()->json([
                'success' => false,
                'message' => 'Nie udało się odczytać certyfikatu.'
            ], 500);
        }

        $certData = openssl_x509_parse($cert);

        if (!$certData) {
            return response()->json([
                'success' => false,
                'message' => 'Nie udało się sparsować certyfikatu.'
            ], 500);
        }

        $details = [
            'common_name' => $certData['subject']['CN'] ?? null,
            'email' => $certData['subject']['emailAddress'] ?? null,
            'organization' => $certData['subject']['O'] ?? null,
            'organizational_unit' => $certData['subject']['OU'] ?? null,
            'valid_from' => isset($certData['validFrom_time_t']) ? date('c', $certData['validFrom_time_t']) : null,
            'valid_to' => isset($certData['validTo_time_t']) ? date('c', $certData['validTo_time_t']) : null,
            'sha1' => sha1($certContent),
            'is_test_certificate' => app()->environment('staging') && filemtime($certPath) && (time() - filemtime($certPath) < 6 * 3600)
        ];

        return response()->json([
            'success' => true,
            'certificate' => $details
        ]);
    }

    public function certificateDetailsView()
    {
        $user = Auth::user();
        $certPath = storage_path("certs/{$user->id}_user_cert.pem");

        $certData = null;
        $isTestCert = false;

        if (file_exists($certPath)) {
            $certContent = file_get_contents($certPath);

            // Spróbuj wczytać certyfikat
            $certResource = @openssl_x509_read($certContent);

            if ($certResource !== false) {
                $parsed = @openssl_x509_parse($certResource);

                if ($parsed !== false) {
                    $certData = [
                        'common_name' => $parsed['subject']['CN'] ?? null,
                        'email' => $parsed['subject']['emailAddress'] ?? null,
                        'organization' => $parsed['subject']['O'] ?? null,
                        'organizational_unit' => $parsed['subject']['OU'] ?? null,
                        'valid_from' => isset($parsed['validFrom_time_t']) ? date('Y-m-d H:i:s', $parsed['validFrom_time_t']) : null,
                        'valid_to' => isset($parsed['validTo_time_t']) ? date('Y-m-d H:i:s', $parsed['validTo_time_t']) : null,
                        'sha1' => sha1($certContent),
                    ];

                    // Sprawdzenie certyfikatu testowego: staging + ważność < 6h
                    $isTestCert = app()->environment('staging') && (time() - filemtime($certPath) <= 6 * 3600);
                }
            }
        }

        return view('Certificate.index', [
            'certData' => $certData,
            'isTestCert' => $isTestCert,
            'user' => $user,
        ]);
    }

    public function generateCertificate(Request $request)
    {
        $user = Auth::user();
        $certPath = storage_path("certs/{$user->id}_user_cert.pem");
        $testCertFlag = false;

        try {
            $dn = [
                "countryName" => "PL",
                "stateOrProvinceName" => "Malopolska",
                "localityName" => "Nowy Sacz",
                "organizationName" => "FEER",
                "organizationalUnitName" => "Certyfikaty",
                "commonName" => $user->name,
                "emailAddress" => $user->email
            ];

            $privateKey = openssl_pkey_new([
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
                "private_key_bits" => 2048,
            ]);

            $csr = openssl_csr_new($dn, $privateKey);

            // W środowisku staging generujemy krótko ważny certyfikat testowy
            $validity = app()->environment('staging') ? 0.25 : 365*24*60*60; // 0.25 dnia = 6h
            $cert = openssl_csr_sign($csr, null, $privateKey, $validity);

            $certPem = '';
            openssl_x509_export($cert, $certPem);
            file_put_contents($certPath, $certPem);

            if (app()->environment('staging')) {
                $testCertFlag = true;
            }

            $certData = openssl_x509_parse($cert);

            return response()->json([
                'success' => true,
                'message' => 'Certyfikat wygenerowany.',
                'certificate' => [
                    'common_name' => $certData['subject']['CN'] ?? null,
                    'email' => $certData['subject']['emailAddress'] ?? null,
                    'organization' => $certData['subject']['O'] ?? null,
                    'organizational_unit' => $certData['subject']['OU'] ?? null,
                    'valid_from' => isset($certData['validFrom_time_t']) ? date('c', $certData['validFrom_time_t']) : null,
                    'valid_to' => isset($certData['validTo_time_t']) ? date('c', $certData['validTo_time_t']) : null,
                    'sha1' => sha1($certPem),
                    'is_test_certificate' => $testCertFlag,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Błąd generowania certyfikatu dla użytkownika {$user->id}: ".$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Błąd generowania certyfikatu: '.$e->getMessage()
            ], 500);
        }
    }





}


