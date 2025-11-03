<?php

namespace Devrabiul\CookieConsent\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CookieConsentController extends Controller
{
    public function scriptUtils(Request $request)
    {
        $script = <<<JS
        window.onload = function() {
            // console.log('Hi');
        };
        JS;

        return response($script, 200)->header('Content-Type', 'application/javascript');
    }
}
