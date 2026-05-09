<?php
namespace App\Core;

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleException(\Throwable $e): void
    {
        self::log($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
        self::render500();
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false; // Suppressed with @operator — let PHP handle it
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::log($error['message'], $error['file'], $error['line']);
            self::render500();
        }
    }

    private static function log(string $message, string $file, int $line, string $trace = ''): void
    {
        $entry = sprintf(
            "[%s] %s in %s on line %d\n%s\n",
            date('Y-m-d H:i:s'),
            $message,
            $file,
            $line,
            $trace
        );
        error_log($entry, 3, __DIR__ . '/../../storage/logs/error.log');
    }

    private static function render500(): void
    {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        // Show a clean page — zero internal details
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
              <title>Something went wrong</title></head><body>
              <h1>Something went wrong</h1>
              <p>We encountered an unexpected error. Please try again later.</p>
              </body></html>';
        exit;
    }
}