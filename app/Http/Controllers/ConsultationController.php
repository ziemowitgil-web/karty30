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

        return view('Consultation.index', compact('consultations'));
    }

    // ================= FORMULARZ ========================
    public function create()
    {
        $clients = Client::orderBy('name')->get();

        // Pobieramy tylko przyszłe potwierdzone rezerwacje
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

        // Jeśli wybrano rezerwację, uzupełniamy dane klienta i czas
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

        // Walidacja dostępności godzin klienta
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

            $testCertFlag = app()->environment('staging')
                && (time() - filemtime(storage_path("app/certificates/".Auth::user()->id."_user_cert.pem")) < 6*3600);

            $xmlContent = $this->generateConsultationXml($consultation, $userCertData, $serverCertData, $testCertFlag);
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

    /**
     * Alias do wywołania podpisu w trybie JSON
     */
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

    // ================= POMOCNICZE ========================
    private function getUserCertificate($userId) { /* ... */ }
    private function getServerCertificate() { /* ... */ }
    private function validateUserCertificate($certData, $userEmail) { /* ... */ }
    private function generateConsultationXml($consultation, $userCertData, $serverCertData, $testCertFlag = false) { /* ... */ }
    private function saveXmlFile($consultation, $xmlContent) { /* ... */ }
    private function generateQrData($consultation) { /* ... */ }
}
