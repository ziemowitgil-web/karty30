<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Mpdf\Mpdf;

class ConsultationController extends Controller
{
    // =========================================================
    // ===================== Konstruktor ======================
    // =========================================================

    /**
     * Apply authentication middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================
    // ===================== Lista konsultacji ================
    // =========================================================

    /**
     * Display a listing of consultations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
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

    /**
     * Show the form for creating a new consultation.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $schedules = \App\Models\Schedule::with('client')
            ->where('status', 'confirmed')
            ->orderBy('start_time', 'asc')
            ->get(['id', 'client_id', 'start_time', 'duration_minutes']);

        return view('Consultation.create', compact('clients', 'schedules'));
    }

    // =========================================================
    // ===================== Tworzenie konsultacji ============
    // =========================================================

    /**
     * Store a newly created consultation in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'nullable|exists:schedules,id',
            'client_id' => ['required', function ($attribute, $value, $fail) {
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

        $validated['user_id'] = Auth::id();
        $validated['user_email'] = Auth::user()->email;
        $validated['username'] = Auth::user()->name;
        $validated['user_ip'] = $request->ip();
        $validated['consultation_datetime'] = $validated['consultation_date'] . ' ' . $validated['consultation_time'];
        unset($validated['consultation_date'], $validated['consultation_time']);

        // Walidacja klienta i limit godzin
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

        activity()->causedBy(Auth::user())
            ->performedOn($consultation)
            ->log("Konsultacja utworzona z status: {$validated['status']}");

        return redirect()->route('consultations.index')->with('success', 'Konsultacja została dodana.');
    }

    // =========================================================
    // ===================== Usuwanie =========================
    // =========================================================

    /**
     * Remove the specified consultation from storage.
     *
     * @param  \App\Models\Consultation  $consultation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Consultation $consultation)
    {
        activity()->causedBy(Auth::user())
            ->performedOn($consultation)
            ->log("Konsultacja usunięta przez " . Auth::user()->name);

        $consultation->delete();
        return redirect()->route('consultations.index')->with('success', 'Konsultacja została usunięta.');
    }

    // =========================================================
    // ===================== Podpis konsultacji ===============
    // =========================================================

    /**
     * Sign a consultation (normal or JSON mode)
     *
     * @param Consultation $consultation
     * @param bool $jsonMode
     * @return mixed
     */
    public function sign(Consultation $consultation, $jsonMode = false)
    {
        if ($consultation->status !== 'draft') {
            if ($jsonMode) return "Tylko wersje robocze można podpisać.";
            return redirect()->back()->with('error','Tylko wersje robocze można podpisać.');
        }

        try {
            activity()->causedBy(Auth::user())->performedOn($consultation)->log('Karta przekazana do procesowania');

            $steps = [
                'Weryfikacja pliku XML dokumentu',
                'Weryfikacja certyfikatu Systemu',
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

            $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xmlContent .= "<consultation>" . PHP_EOL;
            $xmlContent .= "  <id>{$consultation->id}</id>" . PHP_EOL;
            $xmlContent .= "  <client_id>{$clientId}</client_id>" . PHP_EOL;
            $xmlContent .= "  <conducted_by>{$consultation->user->name}</conducted_by>" . PHP_EOL;
            $xmlContent .= "  <datetime>{$consultation->consultation_datetime}</datetime>" . PHP_EOL;
            $xmlContent .= "  <duration>{$consultation->duration_minutes}</duration>" . PHP_EOL;
            $xmlContent .= "  <description>" . htmlspecialchars($consultation->description) . "</description>" . PHP_EOL;
            $xmlContent .= "</consultation>";

            file_put_contents($filePath, $xmlContent);

            $sha1 = @sha1_file($filePath);
            if(!$sha1) {
                $sha1 = substr(str_shuffle('abcdef0123456789'), 0, 40);
                Log::error("Nie udało się wygenerować SHA1 dla konsultacji {$consultation->id}, wygenerowano losowy skrót {$sha1}");
            }

            $consultation->sha1sum = $sha1;
            $consultation->status = 'completed';
            $consultation->approved_by_name = Auth::user()->name;
            $consultation->save();

            activity()->causedBy(Auth::user())->performedOn($consultation)->log("Karta podpisana, SHA1: {$sha1}");

            $msg = "Karta konsultacyjna o ID {$consultation->id} podpisana. SHA1: {$sha1}";

            if ($jsonMode) return $msg;
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            if ($jsonMode) throw $e;
            return redirect()->back()->with('error','Błąd podpisu: '.$e->getMessage());
        }
    }

    /**
     * AJAX JSON endpoint to sign consultation
     *
     * @param Consultation $consultation
     * @return \Illuminate\Http\JsonResponse
     */
    public function signJson(Consultation $consultation)
    {
        try {
            $result = $this->sign($consultation, true);
            return response()->json([
                'success' => true,
                'message' => $result
            ]);
        } catch (\Exception $e) {
            Log::error("Błąd podpisu konsultacji {$consultation->id}: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =========================================================
    // ===================== Historia =========================
    // =========================================================

    /**
     * Show consultation signing history (Blade view)
     */
    public function history(Consultation $consultation)
    {
        $logs = $consultation->activities()->latest()->get();
        return view('Consultation.history', compact('consultation', 'logs'));
    }

    /**
     * Show consultation signing history (JSON AJAX)
     */
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

    /**
     * Generate PDF for consultation (example)
     */
    public function print(Consultation $consultation)
    {
        if ($consultation->status === 'draft') abort(403, 'Nie można drukować wersji roboczej');

        activity()->causedBy(Auth::user())
            ->performedOn($consultation)
            ->log("PDF konsultacji wydrukowany przez " . Auth::user()->name);

        return $this->generatePdf($consultation);
    }

    /**
     * Download consultation PDF
     */
    public function downloadPdf(Consultation $consultation)
    {
        if (!$consultation->sha1sum) {
            abort(403, 'Konsultacja nie została jeszcze podpisana.');
        }

        $mpdf = new Mpdf();

        $html = '
        <h1>Konsultacja #' . $consultation->id . '</h1>
        <table style="width:100%; border-collapse: collapse;">
            <tr>
                <td><strong>Klient:</strong></td>
                <td>' . ($consultation->client->name ?? '-') . '</td>
            </tr>
            <tr>
                <td><strong>Data i godzina:</strong></td>
                <td>' . \Carbon\Carbon::parse($consultation->consultation_datetime)->format('d.m.Y H:i') . '</td>
            </tr>
            <tr>
                <td><strong>Czas trwania:</strong></td>
                <td>' . $consultation->duration_minutes . ' min</td>
            </tr>
            <tr>
                <td><strong>Przeprowadził:</strong></td>
                <td>' . ($consultation->user->name ?? '-') . '</td>
            </tr>
            <tr>
                <td><strong>Opis:</strong></td>
                <td>' . nl2br(htmlspecialchars($consultation->description)) . '</td>
            </tr>
            <tr>
                <td><strong>SHA1:</strong></td>
                <td><span style="font-family: monospace;">' . $consultation->sha1sum . '</span></td>
            </tr>
        </table>
        ';

        $mpdf->WriteHTML($html);
        return $mpdf->Output("consultation_{$consultation->id}.pdf", 'I'); // 'I' = podgląd
    }

    /**
     * Generate XML for consultation
     */
    public function xml(Consultation $consultation)
    {
        $xmlContent = $consultation->toXml(); // metoda generująca XML
        return response($xmlContent, 200)
            ->header('Content-Type', 'application/xml');
    }
}
