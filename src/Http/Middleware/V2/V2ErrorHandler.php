<?php

namespace Motor\Core\Http\Middleware\V2;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Middleware to handle exceptions and format them as V2 API responses.
 *
 * This middleware wraps all errors in the standardized V2 error envelope:
 * {
 *   "error": {
 *     "code": "ERROR_CODE",
 *     "message": "Human-readable message",
 *     "details": { ... } // optional
 *   },
 *   "meta": {
 *     "api_version": "v2"
 *   }
 * }
 */
class V2ErrorHandler
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (AuthorizationException|AccessDeniedHttpException $e) {
            return $this->forbiddenResponse($e->getMessage() ?: 'This action is unauthorized.');
        } catch (AuthenticationException $e) {
            return $this->unauthorizedResponse($e->getMessage() ?: 'Unauthenticated.');
        } catch (ModelNotFoundException|NotFoundHttpException $e) {
            return $this->notFoundResponse($this->getNotFoundMessage($e));
        }
    }

    protected function validationErrorResponse(ValidationException $e): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => $e->getMessage(),
                'details' => $e->errors(),
            ],
            'meta' => [
                'api_version' => 'v2',
            ],
        ], 422);
    }

    protected function forbiddenResponse(string $message): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => $message,
            ],
            'meta' => [
                'api_version' => 'v2',
            ],
        ], 403);
    }

    protected function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => $message,
            ],
            'meta' => [
                'api_version' => 'v2',
            ],
        ], 401);
    }

    protected function notFoundResponse(string $message): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => $message,
            ],
            'meta' => [
                'api_version' => 'v2',
            ],
        ], 404);
    }

    protected function getNotFoundMessage(\Throwable $e): string
    {
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());

            return "{$model} not found";
        }

        return $e->getMessage() ?: 'Resource not found';
    }
}
