<?php

namespace Programic\HttpLogger\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Programic\HttpLogger\Contracts\HttpRequest;
use Programic\HttpLogger\Contracts\LogProfile;
use Programic\HttpLogger\Contracts\LogWriter;
use Symfony\Component\HttpFoundation\Response;

class HttpLogger
{
    protected $logProfile;

    protected $logWriter;

    public function __construct(LogWriter $logWriter, ?LogProfile $logProfile = null)
    {
        $this->logProfile = $logProfile;
        $this->logWriter = $logWriter;
    }

    public function handle(Request $request, Closure $next)
    {
        rescue(function () use ($request) {
            if ($this->logProfile === null || $this->logProfile->shouldLogRequest($request)) {
                $request->headers->set('X-Http-Uuid', Str::uuid());

                $this->logWriter->logRequest($request);
            }
        }, function ($e) {
            $requestModel = app(HttpRequest::class);

            $requestModel::create([
                'request_id' => Str::uuid(),
                'request' => $e->getMessage(),
            ]);
        });


        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($this->logProfile === null || $this->logProfile->shouldLogRequest($request)) {
            $this->logWriter->logResponse($request, $response);
        }
    }
}
