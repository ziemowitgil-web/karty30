<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientBlacklist extends Model
{
    use HasFactory;

    protected $table = 'client_blacklist';

    protected $fillable = [
        'name',
        'reason',
    ];
}
