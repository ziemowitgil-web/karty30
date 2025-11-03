<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Consent</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');

        body {
            font-family: "Inter", sans-serif;
        }
    </style>

    {!! CookieConsent::styles() !!}
</head>

<body>

<!-- Your content -->

{!! CookieConsent::scripts(options: [
    'cookie_lifetime' => config('laravel-cookie-consent.cookie_lifetime', 7),
    'reject_lifetime' => config('laravel-cookie-consent.reject_lifetime', 1),
    'disable_page_interaction' => config('laravel-cookie-consent.disable_page_interaction', true),
    'preferences_modal_enabled' => config('laravel-cookie-consent.preferences_modal_enabled', true),
    'consent_modal_layout' => config('laravel-cookie-consent.consent_modal_layout', 'bar-inline'),
    'flip_button' => config('laravel-cookie-consent.flip_button', true),
    'theme' => config('laravel-cookie-consent.theme', 'default'),
    'cookie_prefix' => config('laravel-cookie-consent.cookie_prefix', 'Laravel_App'),
    'policy_links' => config('laravel-cookie-consent.policy_links', [
        ['text' => 'Privacy Policy', 'link' => url('privacy-policy')],
        ['text' => 'Terms & Conditions', 'link' => url('terms-and-conditions')],
    ]),
    'cookie_categories' => config('laravel-cookie-consent.cookie_categories', [
        'necessary' => [
            'enabled' => true,
            'locked' => true,
            'js_action' => 'loadGoogleAnalytics',
            'title' => 'Essential Cookies',
            'description' => 'These cookies are essential for the website to function properly.',
        ],
        'analytics' => [
            'enabled' => env('COOKIE_CONSENT_ANALYTICS', false),
            'locked' => false,
            'title' => 'Analytics Cookies',
            'description' => 'These cookies help us understand how visitors interact with our website.',
        ],
        'marketing' => [
            'enabled' => env('COOKIE_CONSENT_MARKETING', false),
            'locked' => false,
            'js_action' => 'loadFacebookPixel',
            'title' => 'Marketing Cookies',
            'description' => 'These cookies are used for advertising and tracking purposes.',
        ],
        'preferences' => [
            'enabled' => env('COOKIE_CONSENT_PREFERENCES', false),
            'locked' => false,
            'js_action' => 'loadPreferencesFunc',
            'title' => 'Preferences Cookies',
            'description' => 'These cookies allow the website to remember user preferences.',
        ],
    ]),
    'cookie_modal_title' => 'Cookie Preferences',
    'cookie_modal_intro' => 'You can customize your cookie preferences below.',
    'cookie_accept_btn_text' => 'Accept All',
    'cookie_reject_btn_text' => 'Reject All',
    'cookie_preferences_btn_text' => 'Manage Preferences',
    'cookie_preferences_save_text' => 'Save Preferences',
]) !!}

<script>
    // Example service loader (replace with your actual implementation)
    function loadGoogleAnalytics() {
        // Please put your GA script in loadGoogleAnalytics()
        // You can define function name from - {!! CookieConsent::scripts() !!}

        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'YOUR_GA_ID');

        // Load the GA script
        const script = document.createElement('script');
        script.src = 'https://www.googletagmanager.com/gtag/js?id=YOUR_GA_ID';
        script.async = true;
        document.head.appendChild(script);
    }

    function loadFacebookPixel() {
        // Please put your marketing script in loadFacebookPixel()
        // You can define function name from - {!! CookieConsent::scripts() !!}

        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', 'YOUR_PIXEL_ID');
        fbq('track', 'PageView');
    }
</script>
</body>

</html>