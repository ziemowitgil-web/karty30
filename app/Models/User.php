<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;

class User extends Authenticatable implements WebAuthnAuthenticatable
{
    use HasFactory, Notifiable, WebAuthnAuthentication;

    /**
     * Pola możliwe do masowego wypełnienia.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'webauthn_credential_id',
        'webauthn_public_key',
        'webauthn_counter',
        'document_number',
        'document_issuer',
        'document_type',
    ];

    /**
     * Pola ukryte przy serializacji.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Rzutowania typów atrybutów.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Sprawdza, czy użytkownik ma zarejestrowany klucz WebAuthn.
     */
    public function hasWebauthnKey(): bool
    {
        return !empty($this->webauthn_credential_id);
    }

    /**
     * Certyfikaty użytkownika
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Zwraca aktywny certyfikat użytkownika
     */
    public function activeCertificate()
    {
        return $this->hasOne(Certificate::class)
            ->where('status', 'active')
            ->whereDate('valid_to', '>=', now());
    }

}
