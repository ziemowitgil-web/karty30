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
            $userCertData = $this->getUserCertificate(Auth::id());
            $serverCertData = $this->getServerCertificate();

            if (!$userCertData || !$serverCertData) {
                $msg = "Brak certyfikatu użytkownika lub serwera. Nie można podpisać konsultacji.";
                activity()->causedBy(Auth::user())->performedOn($consultation)->log($msg);
                return $jsonMode ? $msg : redirect()->back()->with('error', $msg);
            }

            // Weryfikacja certyfikatu użytkownika
            $this->validateUserCertificate($userCertData, Auth::user()->email);

            $testCertFlag = app()->environment('staging') && (time() - filemtime(storage_path("app/certificates/".Auth::user()->id."_user_cert.pem")) < 6*3600);

            // Generowanie XML
            $xmlContent = $this->generateConsultationXml($consultation, $userCertData, $serverCertData, $testCertFlag);

            // Zapis pliku XML
            $filePath = $this->saveXmlFile($consultation, $xmlContent);

            // SHA1
            $sha1 = sha1_file($filePath);

            // Aktualizacja konsultacji
            $consultation->update([
                'sha1sum' => $sha1,
                'status' => 'completed',
                'approved_by_name' => Auth::user()->name,
            ]);

            activity()->causedBy(Auth::user())->performedOn($consultation)
                ->log("Konsultacja podpisana (SHA1: {$sha1})");

            $msg = "Konsultacja ID {$consultation->id} podpisana. SHA1: {$sha1}";
            return $jsonMode ? $msg : redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            Log::error("Błąd podpisu konsultacji {$consultation->id}: {$e->getMessage()}");
            return $jsonMode ? $e->getMessage() : redirect()->back()->with('error', 'Błąd podpisu: '.$e->getMessage());
        }
    }

    // =========================================================
    // ===================== PDF ==============================
    // =========================================================
    public function downloadPdf(Consultation $consultation)
    {
        if (!$consultation->sha1sum) abort(403, 'Konsultacja nie została jeszcze podpisana.');

        $userCertData = $this->getUserCertificate(Auth::id());
        $serverCertData = $this->getServerCertificate();

        if (!$userCertData || !$serverCertData) {
            abort(403, 'Brak wymaganych certyfikatów do wygenerowania PDF.');
        }

        $qrData = $this->generateQrData($consultation);

        $mpdf = new Mpdf();
        $html = view('pdf.consultation', [
            'consultation' => $consultation,
            'certificate' => $userCertData,
            'serverCertificate' => $serverCertData,
            'qrImage' => base64_encode(QrCode::format('png')->size(120)->generate($qrData)),
            'printDateTime' => now()->format('d.m.Y H:i:s'),
            'status' => ucfirst($consultation->status),
            'conductedBy' => $consultation->user->name ?? '-'
        ])->render();

        $mpdf->WriteHTML($html);
        return $mpdf->Output("consultation_{$consultation->id}.pdf", 'I');
    }

    // =========================================================
    // ===================== POMOCNICZE ========================
    // =========================================================

    private function getUserCertificate($userId)
    {
        $path = storage_path("app/certificates/{$userId}_user_cert.pem");
        if (!file_exists($path)) return null;

        $cert = openssl_x509_read(file_get_contents($path));
        return $cert ? openssl_x509_parse($cert) : null;
    }

    private function getServerCertificate()
    {
        $path = storage_path("certificates/server.crt");
        if (!file_exists($path)) return null;

        $cert = openssl_x509_read(file_get_contents($path));
        $data = $cert ? openssl_x509_parse($cert) : null;
        if ($data) $data['sha1'] = sha1(file_get_contents($path));
        return $data;
    }

    private function validateUserCertificate($certData, $userEmail)
    {
        $now = time();
        if ($now < $certData['validFrom_time_t'] || $now > $certData['validTo_time_t']) {
            throw new \Exception("Certyfikat użytkownika jest nieważny czasowo.");
        }

        if (strtolower($certData['subject']['emailAddress'] ?? '') !== strtolower($userEmail)) {
            throw new \Exception("Adres e-mail w certyfikacie nie zgadza się z użytkownikiem systemu.");
        }
    }

    private function generateConsultationXml($consultation, $userCertData, $serverCertData, $testCertFlag = false)
    {
        $userCertPath = storage_path("app/certificates/".Auth::user()->id."_user_cert.pem");
        $certSha1 = sha1(file_get_contents($userCertPath));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= "<consultation test_certificate=\"" . ($testCertFlag ? 'true' : 'false') . "\">\n";
        $xml .= "  <id>{$consultation->id}</id>\n";
        $xml .= "  <client_id>{$consultation->client_id}</client_id>\n";
        $xml .= "  <conducted_by>{$consultation->user->name}</conducted_by>\n";
        $xml .= "  <datetime>{$consultation->consultation_datetime}</datetime>\n";
        $xml .= "  <duration>{$consultation->duration_minutes}</duration>\n";
        $xml .= "  <description>" . htmlspecialchars($consultation->description) . "</description>\n";

        // Certyfikat użytkownika
        $xml .= "  <certificate>\n";
        $xml .= "    <common_name>" . htmlspecialchars($userCertData['subject']['CN'] ?? '') . "</common_name>\n";
        $xml .= "    <email>" . htmlspecialchars($userCertData['subject']['emailAddress'] ?? '') . "</email>\n";
        $xml .= "    <organization>" . htmlspecialchars($userCertData['subject']['O'] ?? '') . "</organization>\n";
        $xml .= "    <organizational_unit>" . htmlspecialchars($userCertData['subject']['OU'] ?? '') . "</organizational_unit>\n";
        $xml .= "    <valid_from>" . date('c', $userCertData['validFrom_time_t']) . "</valid_from>\n";
        $xml .= "    <valid_to>" . date('c', $userCertData['validTo_time_t']) . "</valid_to>\n";
        $xml .= "    <sha1>{$certSha1}</sha1>\n";
        $xml .= "  </certificate>\n";

        // QR Code
        $qrBase64 = base64_encode(QrCode::format('png')->size(120)->generate(json_encode([
            'consultation_id' => $consultation->id,
            'sha1' => $consultation->sha1sum,
            'client' => $consultation->client->name ?? 'SYSTEM',
            'date' => $consultation->consultation_datetime
        ])));
        $xml .= "  <qr_base64>{$qrBase64}</qr_base64>\n";

        // Certyfikat serwera
        $xml .= "  <server_certificate>\n";
        foreach($serverCertData as $k => $v){
            if(is_array($v)) $v = json_encode($v);
            $xml .= "    <{$k}>".htmlspecialchars((string)$v)."</{$k}>\n";
        }
        $xml .= "  </server_certificate>\n";

        $xml .= "</consultation>";
        return $xml;
    }

    private function saveXmlFile($consultation, $xmlContent)
    {
        $dir = app_path('signed_docs');
        if (!file_exists($dir)) mkdir($dir, 0777, true);

        $randomStr = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
        $clientId = $consultation->client_id ?? 'SYSTEM';
        $dateStr = date('Ymd_His');
        $fileName = "consultation_{$consultation->id}_{$clientId}_{$dateStr}_{$randomStr}.xml";
        $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;

        file_put_contents($filePath, $xmlContent);
        return $filePath;
    }

    private function generateQrData($consultation)
    {
        return json_encode([
            'consultation_id' => $consultation->id,
            'sha1' => $consultation->sha1sum,
            'client' => $consultation->client->name ?? 'SYSTEM',
            'date' => $consultation->consultation_datetime,
        ]);
    }
}
