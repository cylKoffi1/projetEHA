<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        $this->renderable(function (ValidationException $e, $request) {
            if (!$this->wantsJson($request)) return null;
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->implode(' '),
                'errors'  => $e->errors(),
            ], 422);
        });
    
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if (!$this->wantsJson($request)) return null;
            return response()->json(['success'=>false,'message'=>'Ressource introuvable'], 404);
        });
    
        $this->renderable(function (AuthenticationException $e, $request) {
            if (!$this->wantsJson($request)) return null;
            return response()->json(['success'=>false,'message'=>'Authentification requise'], 401);
        });
    
        $this->renderable(function (AuthorizationException $e, $request) {
            if (!$this->wantsJson($request)) return null;
            return response()->json(['success'=>false,'message'=>'Action non autorisée'], 403);
        });
    
        $this->renderable(function (HttpExceptionInterface $e, $request) {
            if (!$this->wantsJson($request)) return null;
            $status = $e->getStatusCode();
            $msg = match ($status) {
                404 => 'Route introuvable',
                405 => 'Méthode non autorisée',
                429 => 'Trop de requêtes',
                default => 'Erreur',
            };
            return response()->json(['success'=>false,'message'=>$msg], $status);
        });
    
        $this->renderable(function (\Throwable $e, $request) {
            if (!$this->wantsJson($request)) return null;
            \Log::error($e);
            return response()->json(['success'=>false,'message'=>'Erreur serveur'], 500);
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
    protected function wantsJson($request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || str_contains($request->header('Accept', ''), 'application/json');
    }
}
