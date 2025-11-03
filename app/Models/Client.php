<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Exports\ClientsExport;
use Maatwebsite\Excel\Facades\Excel;

class Client extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'problem',
        'equipment',
        'date_of_birth',
        'gender',
        'address',
        'notes',
        'preferred_contact_method',
        'consent',
        'available_days',
        'time_slots',
        'available_hours',
        'used'
    ];

    protected $casts = [
        'consent' => 'boolean',
        'available_days' => 'array',
        'time_slots' => 'array',
        'date_of_birth' => 'date',
        'available_hours' => 'array',
    ];

    /**
     * Konfiguracja Spatie Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'phone', 'status', 'problem', 'equipment',
                'date_of_birth', 'gender', 'address', 'notes',
                'consent', 'preferred_contact_method', 'available_days', 'time_slots', 'available_hours', 'used'
            ])
            ->useLogName('client')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Sumuje wszystkie godziny z available_hours JSON
     */
    public function getAvailableHoursNumberAttribute()
    {
        return is_array($this->available_hours) ? array_sum($this->available_hours) : 0;
    }

    /**
     * Accessor: ID w formacie yy{id}
     */
    public function getFormattedIdAttribute()
    {
        $year = date('y'); // ostatnie 2 cyfry roku
        return $year . '{' . $this->id . '}';
    }

    // ===============================
    // DODATKOWE RELACJE I HELPERY
    // ===============================

    /**
     * Wszystkie konsultacje klienta
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * Ostatnia konsultacja klienta
     */
    public function latestConsultation()
    {
        return $this->hasOne(Consultation::class)->latestOfMany();
    }

    /**
     * Sprawdza czy klient ma dostępne godziny
     */
    public function hasAvailableHours(float $hoursNeeded): bool
    {
        return $this->getAvailableHoursNumberAttribute() - $this->used >= $hoursNeeded;
    }

    // Relacja do terminarzy klienta
    public function schedules()
    {
        return $this->hasMany(\App\Models\Schedule::class, 'client_id');
    }

    // Relacja do activity log (jeśli używasz spatie/laravel-activitylog)
    public function activities()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject');
    }
}
