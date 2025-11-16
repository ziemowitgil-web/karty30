<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Client;
use App\Models\Schedule;
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

    // ================= LISTA ============================
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
        $clients = Client::all();

        return view('Consultation.index', compact('consultations','clients'));
    }

    // ================= SZCZEGÓŁY =========================
    public function details(Consultation $consultation)
    {
        $consultation->load('client', 'user');

        $xmlData = null;
        $xmlPath = storage_path("app/consultations/{$consultation->id}.xml");
        if (file_exists($xmlPath)) {
            try {
                $xmlContent = file_get_contents($xmlPath);
                $xmlData = simplexml_load_string($xmlContent);
            } catch (\Exception $e) {
                $xmlData = null;
            }
        }

        $data = [
            'id' => $consultation->id,
            'client_name' => $consultation->client->name ?? 'SYSTEM',
            'user_name' => $consultation->user->name ?? '-',
            'consultation_datetime' => $consultation->consultation_datetime,
            'duration_minutes' => $consultation->duration_minutes,
            'mode' => $consultation->mode,
            'next_action' => $consultation->next_action,
            'description' => $consultation->description,
            'status' => $consultation->status,
            'sha1sum' => $consultation->sha1sum,
            'approved_by_name' => $consultation->approved_by_name,
            'approved_at' => $consultation->updated_at,
            'xmlData' => $xmlData,
        ];

        return view('Consultation.details', $data);
    }

    // ================= FORMULARZ ========================
    public function create()
    {
        $clients = Client::orderBy('name')->get();

        $schedules = Schedule::with('client')
            ->where('status', 'confirmed')
            ->where('start_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->get();

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

        if ($validated['schedule_id']) {
            $schedule = Schedule::with('client')->find($validated['schedule_id']);
            if ($schedule) {
                $validated['client_id'] = $schedule->client_id;
                $validated['consultation_date'] = $schedule->start_time->format('Y-m-d');
                $validated['consultation_time'] = $schedule->start_time->format('H:i');
                $validated['duration_minutes'] = $schedule->duration_minutes;
            }
        }

        $validated['consultation_datetime'] = $validated['consultation_date'] . ' ' . $validated['consultation_time'];
        unset($validated['consultation_date'], $validated['consultation_time']);

        $validated['user_id'] = Auth::id();
        $validated['user_email'] = Auth::user()->email;
        $validated['username'] = Auth::user()->name;
        $validated['user_ip'] = $request->ip();

        $consultation = Consultation::create($validated);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($consultation)
            ->log("Konsultacja utworzona (status: {$validated['status']})");

        return redirect()->route('consultations.create')->with('success', 'Konsultacja została dodana.');
    }

    // ================= USUWANIE =========================
    public function destroy(Consultation $consultation)
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($consultation)
            ->log("Konsultacja usunięta przez " . Auth::user()->name);

        $consultation->delete();
        return redirect()->route('consultations.index')->with('success', 'Konsultacja została usunięta.');
    }

    // ================= PODPIS ===========================
    public function sign(Consultation $consultation, $jsonMode = false)
    {
        if ($consultation->status !== 'draft') {
            $msg = "Tylko wersje robocze można podpisać.";
            return $jsonMode ? response()->json(['error' => $msg]) : redirect()->back()->with('error', $msg);
        }

        try {
            $userCertData = $this->getUserCertificate(Auth::id());
            $serverCertData = $this->getServerCertificate();

            if (!$userCertData || !$serverCertData) {
                $msg = "Brak certyfikatu użytkownika lub serwera. Nie można podpisać konsultacji.";
                activity()->causedBy(Auth::user())->performedOn($consultation)->log($msg);
                return $jsonMode ? response()->json(['error' => $msg]) : redirect()->back()->with('error', $msg);
            }

            $this->validateUserCertificate($userCertData, Auth::user()->email);

            $xmlContent = $this->generateConsultationXml($consultation, $userCertData, $serverCertData);
            $filePath = $this->saveXmlFile($consultation, $xmlContent);
            $sha1 = sha1_file($filePath);

            $consultation->update([
                'sha1sum' => $sha1,
                'status' => 'completed',
                'approved_by_name' => Auth::user()->name,
            ]);

            activity()->causedBy(Auth::user())->performedOn($consultation)
                ->log("Konsultacja podpisana (SHA1: {$sha1})");

            $msg = "Konsultacja ID {$consultation->id} podpisana. SHA1: {$sha1}";
            return $jsonMode ? response()->json(['success' => $msg]) : redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            Log::error("Błąd podpisu konsultacji {$consultation->id}: {$e->getMessage()}");
            $errMsg = 'Błąd podpisu: '.$e->getMessage();
            return $jsonMode ? response()->json(['error' => $errMsg]) : redirect()->back()->with('error', $errMsg);
        }
    }

    public function signJson(Consultation $consultation)
    {
        return $this->sign($consultation, true);
    }

    // ================= PDF ==============================
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

    // ================= FUNKCJE POMOCNICZE ========================
    private function getUserCertificate($userId)
    {
        $path = storage_path("app/certificates/{$userId}_user_cert.pem");
        return file_exists($path) ? file_get_contents($path) : null;
    }

    private function getServerCertificate()
    {
        $path = storage_path("app/certificates/server_cert.pem");
        return file_exists($path) ? file_get_contents($path) : null;
    }

    private function validateUserCertificate($certData, $userEmail)
    {
        if (empty($certData)) {
            throw new \Exception("Certyfikat użytkownika nie jest dostępny.");
        }
        // Możesz tu dodać walidację zgodnie z regułami (np. sprawdzenie email w certyfikacie)
    }

    private function generateConsultationXml($consultation, $userCertData, $serverCertData)
    {
        $xml = new \SimpleXMLElement('<consultation/>');
        $xml->addChild('id', $consultation->id);
        $xml->addChild('client_name', $consultation->client->name ?? 'SYSTEM');
        $xml->addChild('user_name', $consultation->user->name ?? '-');
        $xml->addChild('datetime', $consultation->consultation_datetime);
        $xml->addChild('duration_minutes', $consultation->duration_minutes);
        $xml->addChild('mode', $consultation->mode);
        $xml->addChild('next_action', $consultation->next_action ?? '');
        $xml->addChild('description', $consultation->description ?? '');
        $xml->addChild('approved_by', $consultation->approved_by_name ?? '');
        $xml->addChild('sha1sum', $consultation->sha1sum ?? '');
        return $xml->asXML();
    }

    private function saveXmlFile($consultation, $xmlContent)
    {
        $dir = storage_path('app/consultations');
        if (!file_exists($dir)) mkdir($dir, 0755, true);
        $path = $dir . "/{$consultation->id}.xml";
        file_put_contents($path, $xmlContent);
        return $path;
    }

    private function generateQrData($consultation)
    {
        return route('consultations.details', $consultation->id) . "#sha1={$consultation->sha1sum}";
    }
}
