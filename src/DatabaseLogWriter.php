<?php

namespace Programic\HttpLogger;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Programic\HttpLogger\Contracts\HttpRequest as HttpRequestContract;
use Programic\HttpLogger\Contracts\LogWriter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class DatabaseLogWriter implements LogWriter
{
    protected $sanitizer;

    public function logRequest(Request $request)
    {
        $message = $this->formatMessage($this->getMessage($request));

        $requestModel = app(HttpRequestContract::class);
//
        $requestModel::create([
            'request_id' => $request->headers->get('X-Http-Uuid'),
            'request' => $message,
        ]);
    }

    public function logResponse(Request $request, Response $response)
    {
        $requestModel = app(HttpRequestContract::class);

        $requestModel::where('request_id', $request->headers->get('X-Http-Uuid'))
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

    public static function fixEncoding(string $s): string
    {
        // removes xD800-xDFFF, x110000 and higher
        return htmlspecialchars_decode(htmlspecialchars($s, ENT_NOQUOTES | ENT_IGNORE, 'UTF-8'), ENT_NOQUOTES);
    }

    protected function formatMessage(array $message)
    {
        $bodyAsJson = self::fixEncoding(json_encode($message['body']));
        $headersAsJson = self::fixEncoding(json_encode($message['headers']));
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
