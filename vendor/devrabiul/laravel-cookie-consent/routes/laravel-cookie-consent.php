<?php

use Illuminate\Support\Facades\Route;
use Devrabiul\CookieConsent\Http\Controllers\CookieConsentController;

Route::controller(CookieConsentController::class)->group(function () {
    Route::get('/laravel-cookie-consent/script-utils', 'scriptUtils')->name('laravel-cookie-consent.script-utils');
});

