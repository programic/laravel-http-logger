<?php

return [

    /*
     * The log profile which determines whether a request should be logged.
     * It should implement `LogProfile`.
     */
    'log_profile' => \Programic\HttpLogger\LogNonGetRequests::class,

    /*
     * The log writer used to write the request to a log.
     * It should implement `LogWriter`.
     */
    'log_writer' => \Programic\HttpLogger\DefaultLogWriter::class,

    /*
     * The log channel used to write the request.
     */
    'log_channel' => env('LOG_CHANNEL', 'stack'),

    /*
     * The log level used to log the request.
     */
    'log_level' => 'info',

    /*
     * Filter out body fields which will never be logged.
     */
    'except' => [
        'password',
        'password_confirmation',
    ],

    /*
     * List of headers that will be sanitized. For example Authorization, Cookie, Set-Cookie...
     */
    'sanitize_headers' => [],

    /*
     * The log database model used to write the request.
     */
    'database_log_model' => \Programic\HttpLogger\Models\HttpRequest::class,
];
