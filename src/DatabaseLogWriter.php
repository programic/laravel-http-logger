<?php

namespace Programic\HttpLogger;

use Programic\HttpLogger\Models\HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class DatabaseLogWriter implements LogWriter
{
    protected $sanitizer;

    public function logRequest(Request $request)
    {
        $message = $this->formatMessage($this->getMessage($request));

        HttpRequest::create([
            'request_id' => $request->headers->get('X-Http-Uuid'),
            'request' => $message,
        ]);
    }

    public function logResponse(Request $request, Response $response)
    {
        HttpRequest::where('request_id', $request->headers->get('X-Http-Uuid'))
            ->update([
                'response' => $response->getContent(),
                'status_code' => $response->getStatusCode(),
                'finished_at' => now(),
            ]);
    }

    public function getMessage(Request $request)
    {
        $files = (new Collection(iterator_to_array($request->files)))
            ->map([$this, 'flatFiles'])
            ->flatten();

        return [
            'method' => strtoupper($request->getMethod()),
            'uri' => $request->getPathInfo(),
            'body' => $request->except(config('http-logger.except')),
            'headers' => $this->getSanitizer()->clean($request->headers->all(), config('http-logger.sanitize_headers')),
            'files' => $files,
        ];
    }

    protected function formatMessage(array $message)
    {
        $bodyAsJson = json_encode($message['body']);
        $headersAsJson = json_encode($message['headers']);
        $files = $message['files']->implode(',');

        return "{$message['method']} {$message['uri']} - Body: {$bodyAsJson} - Headers: {$headersAsJson} - Files: " . $files;
    }

    public function flatFiles($file)
    {
        if ($file instanceof UploadedFile) {
            return $file->getClientOriginalName();
        }
        if (is_array($file)) {
            return array_map([$this, 'flatFiles'], $file);
        }

        return (string) $file;
    }

    protected function getSanitizer()
    {
        if (! $this->sanitizer instanceof Sanitizer) {
            $this->sanitizer = new Sanitizer();
        }

        return $this->sanitizer;
    }
}
