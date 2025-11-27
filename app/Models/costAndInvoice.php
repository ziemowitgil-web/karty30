<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CostAndInvoice extends Model
{
    protected $fillable = [
        'name',
        'service',
        'classes_included',
        'valid_from',
        'valid_to',
        'amount',
        'mpp_number',
    ];

    protected $casts = [
        'classes_included' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'amount' => 'decimal:2',
    ];

    // Automatyczne generowanie MPP
    protected static function booted()
    {
        static::creating(function ($invoice) {
            if (empty($invoice->mpp_number) && $invoice->valid_from) {
                $invoice->mpp_number = 'MPP/' . $invoice->valid_from->format('dmy');
            }
        });
    }

    /**
     * Oblicz koszt zajęć dla określonej liczby zajęć.
     * @param int $numberOfClasses
     * @return float
     */
    public function calculateCost(int $numberOfClasses): float
    {
        // Jeżeli zajęcia wliczone w cenę, koszt = 0
        if ($this->classes_included) {
            return 0;
        }

        return $this->amount * $numberOfClasses;
    }

    /**
     * Sprawdza, czy koszt obowiązuje w danym dniu
     */
    public function isValidOn(Carbon $date): bool
    {
        if ($this->valid_from && $date->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_to && $date->gt($this->valid_to)) {
            return false;
        }
        return true;
    }
}
