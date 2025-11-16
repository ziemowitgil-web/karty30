<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\Schedule;
use App\Models\User;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'client_id',
        'consultation_date',
        'consultation_time',
        'consultation_datetime',
        'duration_minutes',
        'description',
        'status',
        'sign_type',
        'confirmed',
        'next_action',
        'user_id',
        'user_email',
        'username',
        'user_ip',
        'approved_by_name',
        'sha1sum'
    ];

    protected $casts = [
        'consultation_date' => 'date',
        'consultation_time' => 'datetime:H:i',
        'confirmed' => 'boolean',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSha1DisplayAttribute()
    {
        if(env('TEST_MODE', 1)){
            return 'DEMO';
        }
        return $this->sha1sum ?? '-';
    }

    /**
     * Generuje XML konsultacji z dodatkowymi danymi certyfikatu i QR
     *
     * @param array $certData
     *        - common_name
     *        - email
     *        - organization
     *        - organizational_unit
     *        - valid_from
     *        - valid_to
     *        - sha1
     *        - is_test_certificate
     *        - server_certificate (array z openssl_x509_parse)
     * @return string
     */
    public function toXml(array $certData = []): string
    {
        $clientName = $this->client ? $this->client->name : 'SYSTEM';
        $consultationDate = $this->consultation_datetime ?? $this->consultation_date . ' ' . $this->consultation_time;

        // QR Code jako base64
        $qrData = json_encode([
            'consultation_id' => $this->id,
            'sha1' => $this->sha1sum,
            'client' => $clientName,
            'date' => $consultationDate,
        ]);
        $qrBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(120)->generate($qrData));

        $xml = new \SimpleXMLElement('<consultation/>');
        $xml->addChild('id', $this->id);
        $xml->addChild('client', $clientName);
        $xml->addChild('consultation_datetime', $consultationDate);
        $xml->addChild('duration_minutes', $this->duration_minutes);
        $xml->addChild('description', htmlspecialchars($this->description ?? ''));
        $xml->addChild('status', $this->status);
        $xml->addChild('user_name', $this->username ?? '');
        $xml->addChild('user_email', $this->user_email ?? '');
        $xml->addChild('approved_by', $this->approved_by_name ?? '');
        $xml->addChild('sha1sum', $this->sha1sum ?? '');

        // Dodaj dane certyfikatu użytkownika
        $certNode = $xml->addChild('user_certificate');
        foreach (['common_name','email','organization','organizational_unit','valid_from','valid_to','sha1','is_test_certificate'] as $field) {
            $certNode->addChild($field, $certData[$field] ?? '');
        }

        // Dodaj certyfikat serwera jeśli istnieje
        if (!empty($certData['server_certificate'])) {
            $serverNode = $xml->addChild('server_certificate');
            foreach ($certData['server_certificate'] as $key => $value) {
                // zamieniamy tablice wielowymiarowe na string JSON
                if(is_array($value)) $value = json_encode($value);
                $serverNode->addChild($key, htmlspecialchars((string)$value));
            }
        }

        // QR Code
        $xml->addChild('qr_base64', $qrBase64);

        return $xml->asXML();
    }
}
