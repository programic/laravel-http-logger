# Log HTTP requests

[![Latest Version on Packagist](https://img.shields.io/packagist/v/programic/laravel-http-logger.svg?style=flat-square)](https://packagist.org/packages/programic/laravel-http-logger)
[![run-tests](https://github.com/programic/laravel-http-logger/actions/workflows/run-tests.yml/badge.svg)](https://github.com/programic/laravel-http-logger/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/programic/laravel-http-logger.svg?style=flat-square)](https://packagist.org/packages/programic/laravel-http-logger)

This package adds a middleware which can log incoming requests to the default log. 
If anything goes wrong during a user's request, you'll still be able to access the original request data sent by that user.

This log acts as an extra safety net for critical user submissions, such as forms that generate leads.

## Installation

You can install the package via composer:

```bash
composer require programic/laravel-http-logger
```

Optionally you can publish the config file with:

```bash
php artisan vendor:publish --provider="Programic\HttpLogger\HttpLoggerServiceProvider" --tag="config" 
```

Optionally you can publish the migration file with:

```bash
php artisan vendor:publish --provider="Programic\HttpLogger\HttpLoggerServiceProvider" --tag="migrations" 
```


This is the contents of the published config file:

```php
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
];
```

## Usage

This packages provides a middleware which can be added as a global middleware or as a single route.


**Laravel >= 11:**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Programic\HttpLogger\Middlewares\HttpLogger::class);
})
```

**Laravel <= 10:**

```php
// in `app/Http/Kernel.php`

protected $middleware = [
    // ...
    
    \Programic\HttpLogger\Middlewares\HttpLogger::class
];
```

```php
// in a routes file

Route::post('/submit-form', function () {
    //
})->middleware(\Programic\HttpLogger\Middlewares\HttpLogger::class);
```

### Logging

Two classes are used to handle the logging of incoming requests: 
a `LogProfile` class will determine whether the request should be logged,
and `LogWriter` class will write the request to a log. 

A default log implementation is added within this package. 
It will only log `POST`, `PUT`, `PATCH`, and `DELETE` requests 
and it will write to the default Laravel logger.

You're free to implement your own log profile and/or log writer classes, 
and configure it in `config/http-logger.php`.

A custom log profile must implement `\Programic\HttpLogger\LogProfile`. 
This interface requires you to implement `shouldLogRequest`.

```php
// Example implementation from `\Programic\HttpLogger\LogNonGetRequests`

public function shouldLogRequest(Request $request): bool
{
   return in_array(strtolower($request->method()), ['post', 'put', 'patch', 'delete']);
}
```

A custom log writer must implement `\Programic\HttpLogger\LogWriter`. 
This interface requires you to implement `logRequest`.

```php
// Example implementation from `\Programic\HttpLogger\DefaultLogWriter`

public function logRequest(Request $request): void
{
    $method = strtoupper($request->getMethod());
    
    $uri = $request->getPathInfo();
    
    $bodyAsJson = json_encode($request->except(config('http-logger.except')));

    $message = "{$method} {$uri} - {$bodyAsJson}";

    Log::channel(config('http-logger.log_channel'))->info($message);
}
```

#### Hide sensitive headers

You can define headers that you want to sanitize before sending them to the log. 
The most common example would be Authorization header. If you don't want to log jwt token, you can add that header to `http-logger.php` config file:

```php
// in config/http-logger.php

return [
    // ...
    
    'sanitize_headers' => [
        'Authorization'
    ],
];
```

Output would be `Authorization: "****"` instead of `Authorization: "Bearer {token}"`

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/programic/.github/blob/main/CONTRIBUTING.md) for details.

### Security

If you've found a bug regarding security please mail [security@programic.be](mailto:security@programic.be) instead of using the issue tracker.

## Credits

- [Brent Roose](https://github.com/brendt)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
