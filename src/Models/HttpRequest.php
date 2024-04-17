<?php

namespace Programic\HttpLogger\Models;

use Illuminate\Database\Eloquent\Model;

class HttpRequest extends Model
{
    protected $table = 'http_requests';

    protected $fillable = [
        'request_id',
        'request',
        'finished_at',
    ];

    protected $casts = [
        'request' => 'array',
        'finished_at' => 'date',
    ];
}
