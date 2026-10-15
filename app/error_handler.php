<?php

function kordi_register_error_handlers(): void
{
    ini_set('display_errors', '0');
    error_reporting(E_ALL);

    set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    set_exception_handler(static function (Throwable $exception): void {
        kordi_render_error($exception);
    });

    register_shutdown_function(static function (): void {
        $error = error_get_last();
        if ($error === null) {
            return;
        }

        $fatals = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
        if (!in_array($error['type'], $fatals, true)) {
            return;
        }

        kordi_render_error(new ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        ));
    });
}

function kordi_render_error(Throwable $exception): void
{
    static $rendered = false;
    if ($rendered) {
        return;
    }
    $rendered = true;

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    $errorClass = $exception::class;
    $errorMessage = $exception->getMessage();
    $errorFile = $exception->getFile();
    $errorLine = $exception->getLine();
    $errorTrace = $exception->getTraceAsString();

    $relativeFile = $errorFile;
    $documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    if ($documentRoot !== '' && str_starts_with($errorFile, $documentRoot . '/')) {
        $relativeFile = substr($errorFile, strlen($documentRoot) + 1);
    }

    $codeBody = $errorClass . ': ' . $errorMessage . "\n"
        . 'in ' . $relativeFile . ' on line ' . $errorLine . "\n\n"
        . "Stack trace:\n"
        . $errorTrace;

    require __DIR__ . '/Views/error.php';
    exit;
}
