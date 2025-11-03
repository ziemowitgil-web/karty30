<?php

namespace App\Http\Controllers;

use App\Models\ClientBlacklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientBlacklistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // tylko zalogowani użytkownicy
    }

    /**
     * Wyświetla listę klientów na czarnej liście.
     */
    public function index()
    {
        $clients = ClientBlacklist::latest()->paginate(20);
        return view('clients.blacklist.index', compact('clients'));
    }

    /**
     * Formularz dodania klienta do blacklisty.
     */
    public function create()
    {
        return view('clients.blacklist.create');
    }

    /**
     * Zapisanie klienta do blacklisty i logowanie akcji.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'reason' => 'required|string|max:1000',
        ]);

        $client = ClientBlacklist::create($request->only('name', 'reason'));

        // Log aktywności
        activity()
            ->causedBy(Auth::user())          // Kto wykonał akcję
            ->performedOn($client)            // Na jakim obiekcie
            ->withProperties([
                'client_name' => $client->name,
                'reason' => $client->reason
            ])
            ->log('Dodano klienta do blacklisty');

        return redirect()->route('clients.index')
            ->with('success', 'Dodano klienta do CL.');
    }

    /**
     * Usunięcie klienta z blacklisty i logowanie akcji.
     */
    public function destroy(ClientBlacklist $clientBlacklist)
    {
        $clientName = $clientBlacklist->name;
        $clientReason = $clientBlacklist->reason;

        $clientBlacklist->delete();

        // Log aktywności
        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'client_name' => $clientName,
                'reason' => $clientReason
            ])
            ->log('Usunięto klienta z blacklisty');

        return redirect()->route('clients.index')
            ->with('success', 'Usunięto klienta z CL.');
    }
}
