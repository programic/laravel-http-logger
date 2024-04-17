<?php

namespace Programic\HttpLogger\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Programic\HttpLogger\LogProfile;
use Programic\HttpLogger\LogWriter;
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
        if ($this->logProfile === null || $this->logProfile->shouldLogRequest($request)) {
            $request->headers->set('X-Http-Uuid', Str::uuid());

            $this->logWriter->logRequest($request);
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->logWriter->logResponse($request, $response);
    }
}
