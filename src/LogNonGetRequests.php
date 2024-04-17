<?php

namespace Programic\HttpLogger;

use Illuminate\Http\Request;
use Programic\HttpLogger\Contracts\LogProfile;

class LogNonGetRequests implements LogProfile
{
    public function shouldLogRequest(Request $request): bool
    {
        return in_array(strtolower($request->method()), ['post', 'put', 'patch', 'delete']);
    }
}
