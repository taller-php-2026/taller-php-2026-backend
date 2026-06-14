<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ActivityLog extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'activity_logs';

    protected $fillable = [
        'type',
        'action',
        'message',
        'user_id',
        'ip',
        'method',
        'url',
        'status_code',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public $timestamps = false;
}
