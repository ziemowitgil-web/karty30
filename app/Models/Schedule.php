<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'start_time',
        'duration_minutes',
        'status',
        'description',
        'user_id',
    ];

    // Laravel 7+ zaleca używanie $casts zamiast $dates
    protected $casts = [
        'start_time' => 'datetime',
    ];

    // Relacja z klientem
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Label statusu do wyświetlania
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'preliminary' => 'Wstępna',
            'confirmed' => 'Potwierdzona',
            'cancelled' => 'Odwołana',
            'no_show' => 'Nie pojawił się',
            'cancelled_by_feer' => 'Odwołana przez FEER',
            'cancelled_by_client' => 'Odwołana przez Beneficjenta',
            'attended' => 'Obecny',
            default => 'Nieznany',
        };
    }

    // Obliczenie czasu zakończenia
    public function getEndTimeAttribute()
    {
        return $this->start_time->copy()->addMinutes($this->duration_minutes);
    }

}
