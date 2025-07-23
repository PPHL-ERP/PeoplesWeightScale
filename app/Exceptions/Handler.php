<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $exception, $request) {
            // Check if the request expects JSON (API request)
            if ($request->expectsJson()) {
                return $this->handleApiException($exception);
            }
        });
    }

    /**
     * Handle API-specific exception and return JSON response.
     *
     * @param \Throwable $exception
     * @return JsonResponse
     */
    private function handleApiException(Throwable $exception): JsonResponse
    {
        $status = $this->getStatusCode($exception);
        $message = $exception->getMessage();

        // Structure the JSON response
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'code' => $status,
        ], $status);
    }

    /**
     * Get the status code based on the exception type.
     *
     * @param \Throwable $exception
     * @return int
     */
    private function getStatusCode(Throwable $exception): int
    {
        // Default to 500 if the exception doesn't have a specific status code
        return $exception instanceof HttpResponseException
            ? $exception->getStatusCode()
            : ($this->isHttpException($exception)
                ? $exception->getStatusCode()
                : 500);
    }
}
