<?php

namespace Programic\HttpLogger;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface LogWriter
{
    public function logRequest(Request $request);
    public function logResponse(Request $request, Response $response);
}
