<?php

declare(strict_types=1);

namespace PhilipRehberger\ApiResponse;

final class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'OK'): ResponsePayload
    {
        return new ResponsePayload(
            success: true,
            message: $message,
            data: $data,
            statusCode: 200,
        );
    }

    public static function created(mixed $data = null, string $message = 'Created'): ResponsePayload
    {
        return new ResponsePayload(
            success: true,
            message: $message,
            data: $data,
            statusCode: 201,
        );
    }

    public static function noContent(string $message = 'No Content'): ResponsePayload
    {
        return new ResponsePayload(
            success: true,
            message: $message,
            statusCode: 204,
        );
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    public static function error(string $message = 'Error', int $statusCode = 400, ?array $errors = null): ResponsePayload
    {
        return new ResponsePayload(
            success: false,
            message: $message,
            errors: $errors,
            statusCode: $statusCode,
        );
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): ResponsePayload
    {
        return new ResponsePayload(
            success: false,
            message: $message,
            errors: $errors,
            statusCode: 422,
        );
    }

    public static function notFound(string $message = 'Not Found'): ResponsePayload
    {
        return new ResponsePayload(
            success: false,
            message: $message,
            statusCode: 404,
        );
    }

    /**
     * @param  array<int, mixed>  $items
     */
    public static function paginated(array $items, int $total, int $page, int $perPage): ResponsePayload
    {
        return new ResponsePayload(
            success: true,
            message: 'OK',
            data: $items,
            statusCode: 200,
            meta: [
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'last_page' => (int) ceil($total / max(1, $perPage)),
                ],
            ],
        );
    }
}
