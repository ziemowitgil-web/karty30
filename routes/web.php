<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RaportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ClientBlacklistController;
use App\Http\Controllers\Auth\LoginController;
use Laragear\WebAuthn\Http\Controllers\WebAuthnLoginController;
use App\Http\Controllers\WebAuthn\WebAuthnRegisterController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\CertificateController;

/*
|--------------------------------------------------------------------------
| Strona główna
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check() ? redirect('/home') : redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Autoryzacja
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| Accessibility toggle
|--------------------------------------------------------------------------
*/
Route::post('/toggle-accessible', [HomeController::class, 'toggleAccessible'])
    ->name('toggle-accessible');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/home', [HomeController::class, 'index'])->name('home');


/*
|--------------------------------------------------------------------------
| CERTYFIKATY
|--------------------------------------------------------------------------
*/
Route::prefix('certificates')->name('certificates.')->middleware('auth')->group(function () {
    // Lista certyfikatów użytkowników
    Route::get('/', [CertificateController::class, 'index'])->name('index');

    // Podgląd certyfikatu użytkownika
    Route::get('/{userId}/details', [CertificateController::class, 'details'])->name('details');

    // Pobranie certyfikatu / klucza
    Route::get('/{userId}/download/{type}', [CertificateController::class, 'download'])->name('download');

    // Wygenerowanie nowego certyfikatu dla użytkownika
    Route::get('/{userId}/generate', [CertificateController::class, 'showGenerateForm'])->name('generate.form');
    Route::post('/{userId}/generate', [CertificateController::class, 'generate'])->name('generate');

    // Cofnięcie/revokacja certyfikatu
    Route::post('/{userId}/revoke', [CertificateController::class, 'revoke'])->name('revoke');
});

/*
|--------------------------------------------------------------------------
| PANEL UŻYTKOWNIKA (pusta sekcja do rozbudowy)
|--------------------------------------------------------------------------
*/
Route::prefix('user')->name('user.')->middleware('auth')->group(function () {
    // Tutaj dodasz trasy panelu użytkownika w przyszłości
});

/*
|--------------------------------------------------------------------------
| PANEL ADMINISTRATORA
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [AdminServiceController::class, 'dashboard']);
    Route::get('/dashboard', [AdminServiceController::class, 'dashboard'])->name('dashboard');

    // Użytkownicy
    Route::get('/users', [AdminServiceController::class, 'UserList'])->name('users.list');
    Route::get('/users/create', [AdminServiceController::class, 'createUser'])->name('users.create');
    Route::post('/users/store', [AdminServiceController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminServiceController::class, 'editUser'])->name('users.edit');
    Route::patch('/users/{user}', [AdminServiceController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminServiceController::class, 'destroyUser'])->name('users.destroy');

    // Logi
    Route::get('/log', [AdminServiceController::class, 'log'])->name('logs');
    Route::post('/log/clear', [AdminServiceController::class, 'clearLog'])->name('logs.clear');

    // Aktualizacja .env
    Route::post('/env/update', [AdminServiceController::class, 'updateEnv'])->name('env.update');

    // Certyfikaty serwera
    Route::get('/certificate/create', [AdminServiceController::class, 'showCertificateForm'])->name('certificate.form');
    Route::post('/certificate/generate', [AdminServiceController::class, 'generateServerCertificate'])->name('certificate.generate');

    // Koszty i kalkulator
    Route::get('/costs', [AdminServiceController::class, 'CostCalculatorView'])->name('costs.index');
    Route::get('/CostCalculatorEdit/{id}', [AdminServiceController::class, 'CostCalculatorEdit'])
        ->name('costs.edit');
});

/*
|--------------------------------------------------------------------------
| KLIENTY
|--------------------------------------------------------------------------
*/
Route::prefix('clients')->name('clients.')->middleware('auth')->group(function () {
    Route::get('/', [ClientController::class,'index'])->name('index');
    Route::get('/create', [ClientController::class,'create'])->name('create');
    Route::post('/store', [ClientController::class,'store'])->name('store');
    Route::get('/{client}/details', [ClientController::class, 'details'])->name('details');
    Route::get('/{client}/print', [ClientController::class, 'printDocuments'])->name('print');
    Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
    Route::get('/export', [ClientController::class, 'exportXls'])->name('export');
});

/*
|--------------------------------------------------------------------------
| HARMONOGRAM
|--------------------------------------------------------------------------
*/
Route::prefix('schedules')->name('schedules.')->middleware('auth')->group(function () {
    Route::get('/', [ScheduleController::class, 'index'])->name('index');
    Route::get('/create', [ScheduleController::class, 'create'])->name('create');
    Route::post('/', [ScheduleController::class, 'store'])->name('store');
    Route::get('/{schedule}/edit', [ScheduleController::class, 'edit'])->name('edit');
    Route::patch('/{schedule}', [ScheduleController::class, 'update'])->name('update');
    Route::delete('/{schedule}', [ScheduleController::class, 'destroy'])->name('destroy');

    Route::post('/{schedule}/attendance', [ScheduleController::class, 'markAttendance'])->name('markAttendance');
    Route::post('/{schedule}/cancel', [ScheduleController::class, 'cancel'])->name('cancel');
    Route::post('/{schedule}/cancelByFeer', [ScheduleController::class, 'cancelByFeer'])->name('cancelByFeer');
    Route::post('/{schedule}/cancelByClient', [ScheduleController::class, 'cancelByClient'])->name('cancelByClient');

    Route::get('/calendar', [ScheduleController::class, 'calendar'])->name('calendar');
    Route::post('/schedules/check-availability', [ScheduleController::class, 'checkAvailability'])->name('schedules.checkAvailability');

    // Rescheduling
    Route::get('/{schedule}/reschedule', [ScheduleController::class, 'rescheduleForm'])->name('rescheduleForm');
    Route::patch('/{schedule}/reschedule', [ScheduleController::class, 'updateReschedule'])->name('updateReschedule');

    // Blacklista klientów
    Route::prefix('client-blacklist')->name('client_blacklist.')->group(function () {
        Route::get('/', [ClientBlacklistController::class, 'index'])->name('index');
        Route::post('/', [ClientBlacklistController::class, 'store'])->name('store');
        Route::delete('/{clientBlacklist}', [ClientBlacklistController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| KONSULTACJE
|--------------------------------------------------------------------------
*/
Route::prefix('consultations')->name('consultations.')->middleware('auth')->group(function () {
    Route::get('/', [ConsultationController::class, 'index'])->name('index');
    Route::get('/create', [ConsultationController::class, 'create'])->name('create');
    Route::post('/', [ConsultationController::class, 'store'])->name('store');
    Route::delete('/{consultation}', [ConsultationController::class, 'destroy'])->name('destroy');

    Route::post('/{consultation}/sign', [ConsultationController::class, 'sign'])->name('sign');
    Route::get('/{consultation}/history-json', [ConsultationController::class, 'historyJson'])->name('history.json');
    Route::get('/{consultation}/history', [ConsultationController::class, 'history'])->name('history');
    Route::get('/{consultation}/pdf', [ConsultationController::class, 'downloadPdf'])->name('pdf');
    Route::get('/{consultation}/xml', [ConsultationController::class, 'xml'])->name('xml');
    Route::get('/{consultation}/details', [ConsultationController::class, 'details'])->name('details');

    // Certyfikat użytkownika – wsteczna kompatybilność
    Route::get('/certificate/json', [ConsultationController::class, 'certificateDetails'])->name('certificate.json');
    Route::get('/certificate', [ConsultationController::class, 'certificateDetailsView'])->name('certificate.view');
    Route::post('/certificate/generate', [ConsultationController::class, 'generateCertificate'])->name('certificate.generate');
    Route::post('/certificate/access', [ConsultationController::class, 'generateCertificate'])->name('certificate.access');
    Route::post('/certificate/revoke', [ConsultationController::class, 'revokeCertificate'])->name('certificate.revoke');
    Route::get('/certificate/download', [ConsultationController::class, 'downloadCertificate'])->name('certificate.download');
});

/*
|
/*
|--------------------------------------------------------------------------
| RAPORTY
|--------------------------------------------------------------------------
*/
Route::get('/raport', [RaportController::class, 'index'])->name('raport');

Route::prefix('raports')->name('raports.')->middleware('auth')->group(function () {
    Route::get('/cancelled', [RaportController::class, 'cancelledSchedulesReport'])->name('cancelled');
    Route::get('/blacklist', [RaportController::class, 'blacklistReport'])->name('blacklist');
    Route::get('/consultation/approvedthismonth', [RaportController::class, 'approvedThisMonthReport'])->name('approvedThisMonth');
    Route::get('/consultation/approvedlastmonth', [RaportController::class, 'approvedLastMonthReport'])->name('approvedLastMonth');
    Route::get('/consultation/monthlyReportMRPIPS', [RaportController::class, 'monthlyReportMRPIPS'])->name('monthlyReportMRPIPS');
    Route::get('/consultation/monthlyReportMRPIPS/email', [RaportController::class, 'sendMonthlyReportMRPIPS'])->name('monthlyReportMRPIPS.email');
});

/*
|--------------------------------------------------------------------------
| SZYBKA REZERWACJA
|--------------------------------------------------------------------------
*/
Route::get('/s', [ScheduleController::class, 'quickReserve'])->name('quickreservation');
Route::post('/s', [ScheduleController::class, 'quickReserve'])->name('quickreservationstore');

/*
|--------------------------------------------------------------------------
| WEBAUTHN
|--------------------------------------------------------------------------
*/
Route::get('/webauthn/challenge', [WebAuthnLoginController::class, 'showChallengeForm'])->name('webauthn.challenge');
Route::post('/webauthn/challenge', [WebAuthnLoginController::class, 'verifyChallenge'])->name('webauthn.verify');

Route::prefix('webauthn/keys')->name('webauthn.keys.')->middleware('auth')->group(function () {
    Route::get('/', [WebAuthnRegisterController::class, 'index'])->name('index');
    Route::get('/options', [WebAuthnRegisterController::class, 'options'])->name('options');
    Route::post('/register', [WebAuthnRegisterController::class, 'register'])->name('register');
    Route::delete('/{key}', [WebAuthnRegisterController::class, 'destroy'])->name('destroy');
});
