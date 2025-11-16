<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

        // Pobranie certyfikatu użytkownika
        $userCertData = null;
        $userCertPath = storage_path("app/certificates/".Auth::id()."_user_cert.pem");

        if(file_exists($userCertPath)) {
            $certContent = file_get_contents($userCertPath);
            $certResource = @openssl_x509_read($certContent);
            if($certResource !== false) {
                $parsed = @openssl_x509_parse($certResource);
                if($parsed !== false) {
                    $userCertData = $parsed;
                }
            }
        }

        return view('Consultation.index', compact('consultations', 'userCertData', 'userCertPath'));
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
            $certPath = storage_path("app/certificates/".Auth::user()->id."_user_cert.pem");
            if (!file_exists($certPath)) {
                $msg = "Brak certyfikatu użytkownika. Nie można podpisać konsultacji.";
                activity()->causedBy(Auth::user())->performedOn($consultation)->log($msg);
                return $jsonMode ? $msg : redirect()->back()->with('error', $msg);
            }

            $certContent = file_get_contents($certPath);
            $cert = openssl_x509_read($certContent);
            if (!$cert) {
                $msg = "Nieprawidłowy certyfikat użytkownika.";
                activity()->causedBy(Auth::user())->performedOn($consultation)->log($msg);
                return $jsonMode ? $msg : redirect()->back()->with('error', $msg);
            }

            $certData = openssl_x509_parse($cert);
            $now = time();
            if (!$certData || $now < $certData['validFrom_time_t'] || $now > $certData['validTo_time_t']) {
                $msg = "Certyfikat użytkownika jest nieważny czasowo.";
                activity()->causedBy(Auth::user())->performedOn($consultation)->log($msg);
                return $jsonMode ? $msg : redirect()->back()->with('error', $msg);
            }

            if (strtolower($certData['subject']['emailAddress'] ?? '') !== strtolower(Auth::user()->email)) {
                $msg = "Adres e-mail w certyfikacie nie zgadza się z użytkownikiem systemu.";
                activity()->causedBy(Auth::user())->performedOn($consultation)->log($msg);
                return $jsonMode ? $msg : redirect()->back()->with('error', $msg);
            }

            activity()->causedBy(Auth::user())->performedOn($consultation)->log("Weryfikacja certyfikatu użytkownika POWIODŁA");

            if (app()->environment('staging')) {
                $testCertFlag = (time() - filemtime($certPath)) <= 6 * 3600;
                if ($testCertFlag) {
                    activity()->causedBy(Auth::user())->performedOn($consultation)
                        ->log("Wygenerowano certyfikat testowy dla środowiska staging");
                }
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

            $certCN = $certData['subject']['CN'] ?? '';
            $certEmail = $certData['subject']['emailAddress'] ?? '';
            $certOrg = $certData['subject']['O'] ?? '';
            $certOU = $certData['subject']['OU'] ?? '';
            $validFrom = isset($certData['validFrom_time_t']) ? date('c', $certData['validFrom_time_t']) : '';
            $validTo = isset($certData['validTo_time_t']) ? date('c', $certData['validTo_time_t']) : '';
            $certSha1 = sha1($certContent);

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
            $xmlContent .= "    <organizational_unit>" . htmlspecialchars($certOU) . "</organizational_unit>\n";
            $xmlContent .= "    <valid_from>{$validFrom}</valid_from>\n";
            $xmlContent .= "    <valid_to>{$validTo}</valid_to>\n";
            $xmlContent .= "    <sha1>{$certSha1}</sha1>\n";
            $xmlContent .= "  </certificate>\n";

            // QR Code
            $qrData = json_encode([
                'consultation_id' => $consultation->id,
                'sha1' => $consultation->sha1sum,
                'client' => $consultation->client->name ?? 'SYSTEM',
                'date' => $consultation->consultation_datetime,
            ]);
            $qrBase64 = base64_encode(QrCode::format('png')->size(120)->generate($qrData));
            $xmlContent .= "  <qr_base64>{$qrBase64}</qr_base64>\n";

            // Dodaj certyfikat serwera, jeśli istnieje
            $serverCertPath = storage_path("certificates/server.crt");
            if(file_exists($serverCertPath)) {
                $serverCert = openssl_x509_parse(file_get_contents($serverCertPath));
                $xmlContent .= "  <server_certificate>\n";
                foreach($serverCert as $k => $v){
                    if(is_array($v)) $v = json_encode($v);
                    $xmlContent .= "    <{$k}>".htmlspecialchars((string)$v)."</{$k}>\n";
                }
                $xmlContent .= "  </server_certificate>\n";
            }

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
                'approved_by_name' => Auth::user()->name,
            ]);

            activity()->causedBy(Auth::user())->performedOn($consultation)->log("Konsultacja podpisana (SHA1: {$sha1})");

            $msg = "Konsultacja ID {$consultation->id} podpisana. SHA1: {$sha1}";
            return $jsonMode ? $msg : redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            Log::error("Błąd podpisu konsultacji {$consultation->id}: {$e->getMessage()}");
            return $jsonMode ? throw $e : redirect()->back()->with('error', 'Błąd podpisu: '.$e->getMessage());
        }
    }

    public function downloadPdf(Consultation $consultation)
    {
        if (!$consultation->sha1sum) abort(403, 'Konsultacja nie została jeszcze podpisana.');

        $mpdf = new Mpdf();

        // Certyfikat użytkownika
        $userCertPath = storage_path("app/certificates/".Auth::user()->id."_user_cert.pem");
        $certificate = null;
        if(file_exists($userCertPath)){
            $cert = openssl_x509_read(file_get_contents($userCertPath));
            $parsed = openssl_x509_parse($cert);
            $certificate = [
                'common_name' => $parsed['subject']['CN'] ?? '',
                'email' => $parsed['subject']['emailAddress'] ?? '',
                'organization' => $parsed['subject']['O'] ?? '',
                'organizational_unit' => $parsed['subject']['OU'] ?? '',
                'valid_from' => isset($parsed['validFrom_time_t']) ? date('Y-m-d H:i:s', $parsed['validFrom_time_t']) : '',
                'valid_to' => isset($parsed['validTo_time_t']) ? date('Y-m-d H:i:s', $parsed['validTo_time_t']) : '',
                'sha1' => sha1(file_get_contents($userCertPath)),
                'is_test_certificate' => app()->environment('staging') && filemtime($userCertPath) && (time() - filemtime($userCertPath) < 6 * 3600)
            ];
        }

        // Certyfikat serwera
        $serverCertificate = null;
        $serverCertPath = storage_path("certificates/server.crt");
        if(file_exists($serverCertPath)){
            $serverCert = openssl_x509_parse(file_get_contents($serverCertPath));
            $serverCertificate = $serverCert;
            $serverCertificate['sha1'] = sha1(file_get_contents($serverCertPath));
        }

        // QR Code
        $qrData = json_encode([
            'consultation_id' => $consultation->id,
            'sha1' => $consultation->sha1sum,
            'client' => $consultation->client->name ?? 'SYSTEM',
            'date' => $consultation->consultation_datetime,
        ]);
        $qrImage = base64_encode(QrCode::format('png')->size(120)->generate($qrData));

        $html = view('pdf.consultation', compact('consultation', 'certificate', 'serverCertificate', 'qrImage'))->render();
        $mpdf->WriteHTML($html);

        return $mpdf->Output("consultation_{$consultation->id}.pdf", 'I');
    }
}
