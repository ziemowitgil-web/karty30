<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Spatie\Activitylog\Models\Activity;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Domyślne przekierowanie po zalogowaniu
     */
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Logika po udanym logowaniu
     */
    protected function authenticated(Request $request, $user)
    {
        // 1. Weryfikacja WebAuthn
        if ($user->hasWebauthnKey()) {
            Auth::logout();
            session(['webauthn_user_id' => $user->id]);
            return redirect()->route('webauthn.challenge');
        }

        // 2. Sprawdzenie danych dokumentu
        if (!$user->document_type || !$user->document_number || !$user->document_issuer) {
            session(['url.intended' => url()->previous()]);
            return redirect()->route('user.document.form')
                ->with('error', 'Musisz uzupełnić dane dokumentu, aby korzystać z kart konsultacji.');
        }

        // 3. Normalne logowanie – przekierowanie do inteded
        return redirect()->intended($this->redirectPath());
    }

    /**
     * Nadpisanie metody logowania
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Logowanie udanego logowania
            activity('auth')
                ->causedBy($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'logged_at' => now()->toDateTimeString(),
                ])
                ->log('Użytkownik zalogował się');

            return $this->authenticated($request, $user);
        }

        // Logowanie nieudanego logowania (anonimowe)
        activity('auth')
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempted_email' => $request->input('email'),
                'logged_at' => now()->toDateTimeString(),
            ])
            ->log('Nieudana próba logowania');

        return back()->withErrors([
            'email' => 'Nieprawidłowy email lub hasło.',
        ])->withInput($request->only('email'));
    }

    /**
     * Wylogowanie użytkownika z logowaniem akcji
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        activity('auth')
            ->causedBy($user)
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_at' => now()->toDateTimeString(),
            ])
            ->log('Użytkownik wylogował się');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Wyświetlenie formularza dokumentu
     */
    public function showDocumentForm()
    {
        $user = Auth::user();
        return view('auth.document', compact('user'));
    }

    /**
     * Zapis danych dokumentu
     */
    public function storeDocument(Request $request)
    {
        $request->validate([
            'document_type'   => 'required|string|max:255',
            'document_number' => 'required|string|max:255',
            'document_issuer' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->update($request->only('document_type', 'document_number', 'document_issuer'));

        // Po zapisaniu danych dokumentu – wracamy do poprzedniego URL lub dashboard
        $redirectTo = session()->pull('url.intended', $this->redirectPath());

        return redirect($redirectTo)->with('success', 'Dane dokumentu zostały zapisane.');
    }

    /**
     * Wyświetlenie logów logowania
     */
    public function authLogs()
    {
        $activities = Activity::where('log_name', 'auth')->latest()->paginate(20);
        return view('auth.authLogs', compact('activities'));
    }
}
