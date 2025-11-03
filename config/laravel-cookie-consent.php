<?php

/**
 * Cookie Consent Configuration for Internal System: TyfloKonsultacje
 *
 * Uproszczona wersja zgodna z wymogami wewnętrznego systemu ewidencyjnego.
 * Nie wyświetla banera w środowiskach developerskich ani testowych.
 * Aktywna wyłącznie w środowisku produkcyjnym.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Prefix i aktywacja
    |--------------------------------------------------------------------------
    | Dla spójności logów i ciasteczek w systemie wewnętrznym.
    */
    'cookie_prefix' => env('APP_NAME', 'TyfloKonsultacje'),

    /**
     * Aktywacja tylko na produkcji
     */
    'enabled' => env('APP_ENV') === 'production',

    /**
     * Okres ważności zgody (dni)
     */
    'cookie_lifetime' => 365,

    /**
     * Czas zapamiętania odmowy (dni)
     */
    'reject_lifetime' => 30,

    /**
     * Styl i zachowanie — minimalistyczny pasek u dołu strony
     */
    'consent_modal_layout' => 'bar-inline',
    'preferences_modal_enabled' => false,
    'preferences_modal_layout' => 'box',
    'flip_button' => false,
    'disable_page_interaction' => false,

    /**
     * Motyw — jasny, dyskretny
     */
    'theme' => 'light',

    /**
     * Treści komunikatów — dostosowane do systemu wewnętrznego
     */
    'cookie_title' => 'Informacja o plikach cookie',
    'cookie_description' => 'System TyfloKonsultacje wykorzystuje wyłącznie niezbędne pliki cookie w celu utrzymania sesji i bezpieczeństwa użytkowników. Dane nie są wykorzystywane do celów marketingowych ani analitycznych.',

    'cookie_accept_btn_text' => 'Rozumiem',
    'cookie_reject_btn_text' => 'Odrzuć',
    'cookie_preferences_btn_text' => 'Ustawienia',
    'cookie_preferences_save_text' => 'Zapisz ustawienia',
    'cookie_modal_title' => 'Ustawienia prywatności',
    'cookie_modal_intro' => 'System wykorzystuje tylko pliki cookie niezbędne do działania aplikacji.',

    /**
     * Kategorie cookie — ograniczone tylko do technicznych
     */
    'cookie_categories' => [
        'necessary' => [
            'enabled' => true,
            'locked' => true,
            'title' => 'Pliki niezbędne',
            'description' => 'Ciasteczka wymagane do działania systemu, m.in. sesja zalogowanego użytkownika.',
        ],
    ],

    /**
     * Linki do dokumentów — wewnętrzne polityki
     */
    'policy_links' => [
        [
            'text' => 'Polityka prywatności',
            'link' => env('APP_URL') . '/polityka-prywatnosci',
        ],
        [
            'text' => 'Regulamin systemu',
            'link' => env('APP_URL') . '/regulamin',
        ],
    ],

];
