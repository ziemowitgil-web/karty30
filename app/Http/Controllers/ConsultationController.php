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

            $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xmlContent .= "<consultation>\n";
            $xmlContent .= "  <id>{$consultation->id}</id>\n";
            $xmlContent .= "  <client_id>{$clientId}</client_id>\n";
            $xmlContent .= "  <conducted_by>{$consultation->user->name}</conducted_by>\n";
            $xmlContent .= "  <datetime>{$consultation->consultation_datetime}</datetime>\n";
            $xmlContent .= "  <duration>{$consultation->duration_minutes}</duration>\n";
            $xmlContent .= "  <description>" . htmlspecialchars($consultation->description) . "</description>\n";
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
                'sha1' => $consultation->sha1sum
            ]);
        } catch (\Exception $e){
            Log::error("Błąd podpisu konsultacji {$consultation->id}: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
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

}
