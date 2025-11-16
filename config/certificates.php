<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ścieżki do certyfikatów
    |--------------------------------------------------------------------------
    */
    'user_path' => base_path('app/certificates'), // katalog z certyfikatami użytkowników
    'server_cert' => storage_path('app/certificates/server_cert.pem'), // certyfikat serwera

    /*
    |--------------------------------------------------------------------------
    | Dane serwera (statyczne)
    |--------------------------------------------------------------------------
    */
    'server_dn' => [
        'C'  => 'PL',
        'ST' => 'Mazowieckie',
        'L'  => 'Warszawa',
        'O'  => 'TwojaFirma',
        'OU' => 'Dział Serwera',
        'CN' => 'Serwer Konsultacji',
        'emailAddress' => 'server@twojafirma.pl',
    ],

    /*
    |--------------------------------------------------------------------------
    | Szablon DN dla użytkownika
    |--------------------------------------------------------------------------
    | Pola, które będą używane przy generowaniu certyfikatu użytkownika.
    | CN i emailAddress będą nadpisywane na podstawie danych użytkownika.
    */
    'user_dn_template' => [
        'C'  => 'PL',
        'ST' => 'Małopolskie',
        'L'  => 'Nowy Sącz',
        'O'  => 'FEER',
        'OU' => 'testy',
        // CN i emailAddress będą dynamicznie nadpisywane
    ],

    /*
    |--------------------------------------------------------------------------
    | Inne ustawienia
    |--------------------------------------------------------------------------
    | Możesz tu dodać np. czas ważności certyfikatu w dniach.
    */
    'valid_days' => 365,
];

