<?php

namespace Motor\Core\Http\Traits\V2;

use Illuminate\Http\JsonResponse;

trait HandlesApiErrors
{
    protected function errorResponse(
        string $code,
        string $message,
        int $statusCode,
        array $details = []
    ): JsonResponse {
        $response = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
            'meta' => [
                'api_version' => 'v2',
            ],
        ];

        if (! empty($details)) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $statusCode);
    }

    public function validationErrorResponse(array $errors, string $message = 'The given data was invalid.'): JsonResponse
    {
        return $this->errorResponse('VALIDATION_ERROR', $message, 422, $errors);
    }

    public function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse('NOT_FOUND', $message, 404);
    }

    public function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse('UNAUTHORIZED', $message, 401);
    }

    public function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse('FORBIDDEN', $message, 403);
    }

    public function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse('SERVER_ERROR', $message, 500);
    }
}
