<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\ClientBlacklist;
use App\Models\Consultation;
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
     * Raport miesięczny MRPiPS w PDF (szyfrowany) lub CSV.
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
     * Wysyłka raportu MRPiPS mailem (PDF szyfrowany hasłem).
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
}
