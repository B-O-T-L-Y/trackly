<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'type',
        'ts',
        'session_id',
        'idempotency_key'
    ];

    protected $casts = [
        'ts' => 'datetime',
    ];
}
