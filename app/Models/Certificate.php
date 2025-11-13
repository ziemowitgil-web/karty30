<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'organization',
        'valid_from',
        'valid_to',
        'status',
        'certificate_number',
        'notes',
    ];

    /**
     * Powiązanie z użytkownikiem
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sprawdza, czy certyfikat jest aktywny
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
            (!$this->valid_to || $this->valid_to >= now()->toDateString());
    }
}
