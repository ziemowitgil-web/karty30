<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\ClientBlacklist;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\MonthlyReportMRPIPS;

class RaportController extends Controller
{
    /**
     * Wyświetla stronę z wyborem raportów.
     */
    public function index()
    {
        return view('Raport.index');
    }

    /**
     * Raport odwołanych terminów.
     */
    public function cancelledSchedulesReport(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Schedule::query()->whereIn('status', ['cancelled_by_feer', 'cancelled_by_client']);
        if ($from) $query->whereDate('start_time', '>=', $from);
        if ($to) $query->whereDate('start_time', '<=', $to);

        $cancelledByFeer = (clone $query)->where('status', 'cancelled_by_feer')->count();
        $cancelledByClient = (clone $query)->where('status', 'cancelled_by_client')->count();
        $total = (clone $query)->count();

        return view('Raport.canceled', [
            'total' => $total,
            'cancelled_by_feer' => $cancelledByFeer,
            'cancelled_by_client' => $cancelledByClient,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Raport klientów na czarnej liście.
     */
    public function blacklistReport(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $query = ClientBlacklist::query();
        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);

        $blacklistedClients = $query->orderBy('created_at', 'desc')->get();
        $total = $blacklistedClients->count();

        return view('Raport.blacklist', [
            'total' => $total,
            'clients' => $blacklistedClients,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Raport zatwierdzonych konsultacji w bieżącym miesiącu.
     */
    public function approvedThisMonthReport()
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;

        $consultations = Consultation::with(['client', 'user'])
            ->where('status', 'completed')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->get();

        return view('Raport.approved_this_month', [
            'total' => $consultations->count(),
            'consultations' => $consultations,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Raport zatwierdzonych konsultacji w poprzednim miesiącu.
     */
    public function approvedLastMonthReport()
    {
        $lastMonth = now()->subMonth();
        $month = $lastMonth->month;
        $year = $lastMonth->year;

        $consultations = Consultation::with(['client', 'user'])
            ->where('status', 'completed')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->get();

        return view('Raport.approved_last_month', [
            'total' => $consultations->count(),
            'consultations' => $consultations,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Raport miesięczny MRPiPS w PDF lub CSV.
     */
    public function monthlyReportMRPIPS($year = null, $month = null, $format = 'pdf')
    {
        $now = now();
        $month = $month ?? $now->month;
        $year = $year ?? $now->year;

        $consultations = Consultation::with(['client', 'user'])
            ->where('status', 'completed')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->get();

        $data = [
            'month' => $month,
            'year' => $year,
            'consultations' => $consultations,
            'total' => $consultations->count(),
            'generated_at' => now()->format('d.m.Y H:i'),
            'generated_by' => auth()->user()->name ?? 'System',
        ];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('Raport.mrpips_monthly', $data);
            return $pdf->download("raport_miesieczny_{$year}_{$month}.pdf");
        }

        if ($format === 'csv') {
            $filename = "raport_miesieczny_{$year}_{$month}.csv";
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($consultations) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['ID', 'Klient', 'Data i godzina', 'Czas trwania', 'Przeprowadził']);
                foreach ($consultations as $c) {
                    fputcsv($handle, [
                        $c->id,
                        $c->client->name ?? '-',
                        $c->consultation_datetime,
                        $c->duration_minutes,
                        $c->user->name ?? '-',
                    ]);
                }
                fclose($handle);
            };

            return Response::stream($callback, 200, $headers);
        }

        abort(400, 'Nieobsługiwany format raportu.');
    }

    /**
     * Wysyłka raportu MRPiPS mailem.
     */
    public function sendMonthlyReportMRPIPS($year = null, $month = null)
    {
        $now = now();
        $month = $month ?? $now->month;
        $year = $year ?? $now->year;

        $consultations = Consultation::with(['client', 'user'])
            ->where('status', 'completed')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->get();

        $data = [
            'consultations' => $consultations,
            'month' => $month,
            'year' => $year,
            'generated_by' => auth()->user()->name ?? 'System',
            'generated_at' => now()->format('d.m.Y H:i'),
        ];

        $pdf = Pdf::loadView('Raport.mrpips_monthly', $data);

        Mail::to('ziemowit.gil@feer.org.pl')
            ->send(new MonthlyReportMRPIPS($pdf, $month, $year, $data));

        return redirect()->back()->with('success', 'Raport PDF został wysłany mailem.');
    }

    // ----------------------
    // NOWE RAPORTY
    // ----------------------

    /**
     * Raport konsultacji według użytkownika.
     */
    public function consultationsByUser(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $consultations = Consultation::with('user')
            ->where('status', 'completed')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->get();

        $report = $consultations->groupBy(fn($c) => $c->user->name ?? 'Nieprzypisany')
            ->map(fn($group) => $group->count());

        return view('Raport.consultations_by_user', [
            'report' => $report,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Raport konsultacji według typu/usługi.
     */
    public function consultationsByType(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $consultations = Consultation::where('status', 'completed')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->get();

        $report = $consultations->groupBy('type')->map(fn($group) => $group->count());

        return view('Raport.consultations_by_type', [
            'report' => $report,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Raport klientów nowych vs powracających.
     */
    public function newVsReturningClients(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $consultations = Consultation::with('client')
            ->where('status', 'completed')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->get();

        $clientsGrouped = $consultations->groupBy('client_id');

        $newClients = $clientsGrouped->filter(fn($group) => $group->first()->client->created_at->format('m.Y') == sprintf('%02d.%d', $month, $year))->count();
        $returningClients = $clientsGrouped->count() - $newClients;

        return view('Raport.new_vs_returning_clients', [
            'newClients' => $newClients,
            'returningClients' => $returningClients,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Raport aktywnych klientów w danym miesiącu.
     */
    public function activeClientsReport(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $clients = Consultation::with('client')
            ->where('status', 'completed')
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->get()
            ->pluck('client')
            ->unique('id');

        return view('Raport.active_clients', [
            'clients' => $clients,
            'total' => $clients->count(),
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Raport odwołań według użytkownika.
     */
    public function cancelledByUserReport(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Schedule::query()->whereIn('status', ['cancelled_by_feer', 'cancelled_by_client']);
        if ($from) $query->whereDate('start_time', '>=', $from);
        if ($to) $query->whereDate('start_time', '<=', $to);

        $report = $query->with('user')->get()
            ->groupBy(fn($s) => $s->user->name ?? 'Nieprzypisany')
            ->map(fn($group) => $group->count());

        return view('Raport.cancelled_by_user', [
            'report' => $report,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
