# PHP API Response

[![Tests](https://github.com/philiprehberger/php-api-response/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-api-response/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-api-response.svg)](https://packagist.org/packages/philiprehberger/php-api-response)
[![Last updated](https://img.shields.io/github/last-commit/philiprehberger/php-api-response)](https://github.com/philiprehberger/php-api-response/commits/main)

Standardized API response builder for consistent JSON APIs.

## Requirements

- PHP 8.2+

## Installation

```bash
composer require philiprehberger/php-api-response
```

## Usage

### Success Responses

```php
use PhilipRehberger\ApiResponse\ApiResponse;

// Basic success
$response = ApiResponse::success();
// {"success": true, "message": "OK", "data": null}

// Success with data
$response = ApiResponse::success(['id' => 1, 'name' => 'John']);
// {"success": true, "message": "OK", "data": {"id": 1, "name": "John"}}

// Created
$response = ApiResponse::created(['id' => 42]);
// {"success": true, "message": "Created", "data": {"id": 42}}

// No content
$response = ApiResponse::noContent();

// Accepted (202) — request queued for async processing
$response = ApiResponse::accepted(['job_id' => 'abc-123']);
// {"success": true, "message": "Accepted", "data": {"job_id": "abc-123"}}
// {"success": true, "message": "No Content", "data": null}
```

### Error Responses

```php
// Generic error
$response = ApiResponse::error('Something went wrong', 500);
// {"success": false, "message": "Something went wrong", "data": null}

// Not found
$response = ApiResponse::notFound('User not found');
// {"success": false, "message": "User not found", "data": null}

// Unauthorized (401)
$response = ApiResponse::unauthorized('Token expired');

// Forbidden (403)
$response = ApiResponse::forbidden('Insufficient permissions');

// Internal server error (500)
$response = ApiResponse::internalServerError('Database unavailable');

// Validation error
$response = ApiResponse::validationError([
    'email' => ['The email field is required.'],
    'name' => ['The name must be at least 2 characters.'],
]);
// {"success": false, "message": "Validation failed", "data": null, "errors": {"email": [...], "name": [...]}}
```

### Paginated Responses

```php
$response = ApiResponse::paginated(
    items: $users,
    total: 150,
    page: 2,
    perPage: 25,
);
// {"success": true, "message": "OK", "data": [...], "meta": {"pagination": {"total": 150, "page": 2, "per_page": 25, "last_page": 6}}}
```

### Serialization

`ResponsePayload` implements `JsonSerializable` and `Stringable`:

```php
$response = ApiResponse::success(['key' => 'value']);

// Convert to array
$array = $response->toArray();

// Convert to JSON string
$json = $response->toJson();
$json = $response->toJson(JSON_PRETTY_PRINT);

// Use with json_encode directly
$json = json_encode($response);

// Cast to string
$string = (string) $response;
```

### Using with Laravel

Return responses directly from controllers by accessing the payload properties:

```php
public function index(): JsonResponse
{
    $users = User::paginate(25);

    $payload = ApiResponse::paginated(
        items: $users->items(),
        total: $users->total(),
        page: $users->currentPage(),
        perPage: $users->perPage(),
    );

    return response()->json($payload->toArray(), $payload->statusCode);
}
```

### Fluent Chaining

All `with*` methods return a new `ResponsePayload` instance, keeping the original unchanged:

```php
$response = ApiResponse::success(['id' => 1, 'name' => 'John'])
    ->withMeta(['request_id' => 'abc-123', 'version' => '2.0'])
    ->withHeaders(['X-Request-Id' => 'abc-123'])
    ->withStatusCode(202);

// Merge additional metadata onto a paginated response
$response = ApiResponse::paginated($users, total: 150, page: 2, perPage: 25)
    ->withMeta(['cache' => 'hit']);

// Attach pagination to any success response after the fact
$response = ApiResponse::success($users)
    ->withPagination(total: 150, page: 2, perPage: 25);

// Attach headers for use in your framework's response
$payload = ApiResponse::created(['id' => 42])
    ->withHeaders(['Location' => '/users/42']);

return response()->json($payload->toArray(), $payload->statusCode)
    ->withHeaders($payload->headers);
```

### Response Shape

All responses follow a consistent structure:

```json
{
    "success": true,
    "message": "OK",
    "data": null,
    "errors": {},
    "meta": {}
}
```

- `success` (bool) - Always present
- `message` (string) - Always present
- `data` (mixed) - Always present
- `errors` (object) - Only present when there are errors
- `meta` (object) - Only present when metadata is provided (e.g., pagination)

## API

| Method | Status Code | Description |
|--------|-------------|-------------|
| `ApiResponse::success($data, $message)` | 200 | Successful response with optional data |
| `ApiResponse::created($data, $message)` | 201 | Resource created successfully |
| `ApiResponse::noContent($message)` | 204 | Success with no response body |
| `ApiResponse::error($message, $statusCode, $errors)` | 400 | Generic error response |
| `ApiResponse::validationError($errors, $message)` | 422 | Validation failure with field errors |
| `ApiResponse::notFound($message)` | 404 | Resource not found |
| `ApiResponse::unauthorized($message, $errors)` | 401 | Authentication required or failed |
| `ApiResponse::forbidden($message, $errors)` | 403 | Authenticated but not permitted |
| `ApiResponse::accepted($data, $message)` | 202 | Request accepted for asynchronous processing |
| `ApiResponse::internalServerError($message, $errors)` | 500 | Unexpected server-side failure |
| `ApiResponse::paginated($items, $total, $page, $perPage)` | 200 | Paginated list with metadata |

### Fluent Methods on `ResponsePayload`

| Method | Description |
|--------|-------------|
| `withMeta(array $meta)` | Returns a new instance with merged metadata |
| `withHeaders(array $headers)` | Returns a new instance with custom response headers |
| `withStatusCode(int $code)` | Returns a new instance with overridden HTTP status code |
| `withPagination(int $total, int $page, int $perPage)` | Returns a new instance with a `pagination` block merged into meta |

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## Support

If you find this project useful:

⭐ [Star the repo](https://github.com/philiprehberger/php-api-response)

🐛 [Report issues](https://github.com/philiprehberger/php-api-response/issues?q=is%3Aissue+is%3Aopen+label%3Abug)

💡 [Suggest features](https://github.com/philiprehberger/php-api-response/issues?q=is%3Aissue+is%3Aopen+label%3Aenhancement)

❤️ [Sponsor development](https://github.com/sponsors/philiprehberger)

🌐 [All Open Source Projects](https://philiprehberger.com/open-source-packages)

💻 [GitHub Profile](https://github.com/philiprehberger)

🔗 [LinkedIn Profile](https://www.linkedin.com/in/philiprehberger)

## License

[MIT](LICENSE)
