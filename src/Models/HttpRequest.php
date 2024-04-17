<?php

namespace Programic\HttpLogger\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class HttpRequest extends Model
{
    use Prunable;

    protected $table = 'http_requests';

    protected $fillable = [
        'request_id',
        'request',
        'response',
        'status_code',
        'finished_at',
    ];

    protected $casts = [
        'request' => 'array',
        'finished_at' => 'date',
    ];

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subWeek());
    }
}
