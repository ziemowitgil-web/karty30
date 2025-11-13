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
    // ... istniejące metody ...

    /**
     * Raport konsultacji według użytkownika w wybranym miesiącu.
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
     * Raport klientów nowych vs powracających w miesiącu.
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
