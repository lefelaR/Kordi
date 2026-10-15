<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'company',
        'location',
        'event_date',
        'status',
        'finance',
        'attendants',
        'revenue',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'attendants' => 'integer',
            'revenue' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }
}
