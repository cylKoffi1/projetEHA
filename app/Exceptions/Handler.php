<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Pour toutes les requêtes API/AJAX, retourner du JSON
        if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
            $status = $this->isHttpException($exception) ? $exception->getStatusCode() : 500;
            
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];

            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $exception->errors()
                ], 422);
            }

            if (config('app.debug')) {
                $response['error'] = $exception->getTraceAsString();
                $response['file'] = $exception->getFile();
                $response['line'] = $exception->getLine();
            }

            return response()->json($response, $status);
        }

        return parent::render($request, $exception);
    }

}
