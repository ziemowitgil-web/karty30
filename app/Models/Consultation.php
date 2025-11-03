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
        'consultation_datetime', // <--- dodane
        'duration_minutes',
        'description',
        'status',
        'sign_type',
        'confirmed',
        'next_action',
        'user_id',          // <--- dodane
        'user_email',       // <--- dodane
        'username',         // <--- dodane
        'user_ip',          // <--- dodane
        'approved_by_name', // <--- dodane
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

}
