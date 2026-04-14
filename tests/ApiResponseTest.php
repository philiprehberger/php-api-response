<?php

declare(strict_types=1);

namespace PhilipRehberger\ApiResponse\Tests;

use PhilipRehberger\ApiResponse\ApiResponse;
use PhilipRehberger\ApiResponse\ResponsePayload;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ApiResponseTest extends TestCase
{
    #[Test]
    public function test_success_response_structure(): void
    {
        $response = ApiResponse::success();

        $this->assertTrue($response->success);
        $this->assertSame('OK', $response->message);
        $this->assertNull($response->data);
        $this->assertSame(200, $response->statusCode);
        $this->assertNull($response->errors);
        $this->assertNull($response->meta);

        $array = $response->toArray();
        $this->assertArrayHasKey('success', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayNotHasKey('errors', $array);
        $this->assertArrayNotHasKey('meta', $array);
    }

    #[Test]
    public function test_success_with_data(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $response = ApiResponse::success($data);

        $this->assertTrue($response->success);
        $this->assertSame($data, $response->data);
        $this->assertSame(200, $response->statusCode);
    }

    #[Test]
    public function test_success_with_custom_message(): void
    {
        $response = ApiResponse::success(message: 'All good');

        $this->assertSame('All good', $response->message);
    }

    #[Test]
    public function test_created_response(): void
    {
        $data = ['id' => 42];
        $response = ApiResponse::created($data);

        $this->assertTrue($response->success);
        $this->assertSame('Created', $response->message);
        $this->assertSame($data, $response->data);
        $this->assertSame(201, $response->statusCode);
    }

    #[Test]
    public function test_no_content_response(): void
    {
        $response = ApiResponse::noContent();

        $this->assertTrue($response->success);
        $this->assertSame('No Content', $response->message);
        $this->assertNull($response->data);
        $this->assertSame(204, $response->statusCode);
    }

    #[Test]
    public function test_error_response(): void
    {
        $response = ApiResponse::error();

        $this->assertFalse($response->success);
        $this->assertSame('Error', $response->message);
        $this->assertSame(400, $response->statusCode);
        $this->assertNull($response->errors);
    }

    #[Test]
    public function test_error_with_custom_status_code(): void
    {
        $errors = ['detail' => 'Forbidden resource'];
        $response = ApiResponse::error('Forbidden', 403, $errors);

        $this->assertFalse($response->success);
        $this->assertSame('Forbidden', $response->message);
        $this->assertSame(403, $response->statusCode);
        $this->assertSame($errors, $response->errors);

        $array = $response->toArray();
        $this->assertArrayHasKey('errors', $array);
    }

    #[Test]
    public function test_validation_error_response(): void
    {
        $errors = [
            'email' => ['The email field is required.'],
            'name' => ['The name field is required.'],
        ];
        $response = ApiResponse::validationError($errors);

        $this->assertFalse($response->success);
        $this->assertSame('Validation failed', $response->message);
        $this->assertSame(422, $response->statusCode);
        $this->assertSame($errors, $response->errors);
    }

    #[Test]
    public function test_not_found_response(): void
    {
        $response = ApiResponse::notFound();

        $this->assertFalse($response->success);
        $this->assertSame('Not Found', $response->message);
        $this->assertSame(404, $response->statusCode);
    }

    #[Test]
    public function test_paginated_response_structure(): void
    {
        $items = [['id' => 1], ['id' => 2]];
        $response = ApiResponse::paginated($items, total: 50, page: 1, perPage: 10);

        $this->assertTrue($response->success);
        $this->assertSame($items, $response->data);
        $this->assertSame(200, $response->statusCode);
        $this->assertNotNull($response->meta);

        $array = $response->toArray();
        $this->assertArrayHasKey('meta', $array);
        $this->assertArrayHasKey('pagination', $array['meta']);
        $this->assertSame(50, $array['meta']['pagination']['total']);
        $this->assertSame(1, $array['meta']['pagination']['page']);
        $this->assertSame(10, $array['meta']['pagination']['per_page']);
        $this->assertSame(5, $array['meta']['pagination']['last_page']);
    }

    #[Test]
    public function test_paginated_calculates_last_page(): void
    {
        $response = ApiResponse::paginated([], total: 25, page: 1, perPage: 10);

        $meta = $response->toArray()['meta']['pagination'];
        $this->assertSame(3, $meta['last_page']);

        $response2 = ApiResponse::paginated([], total: 0, page: 1, perPage: 10);
        $meta2 = $response2->toArray()['meta']['pagination'];
        $this->assertSame(0, $meta2['last_page']);

        // Edge case: perPage of 0 should not divide by zero
        $response3 = ApiResponse::paginated([], total: 10, page: 1, perPage: 0);
        $meta3 = $response3->toArray()['meta']['pagination'];
        $this->assertSame(10, $meta3['last_page']);
    }

    #[Test]
    public function test_response_payload_to_json(): void
    {
        $response = ApiResponse::success(['key' => 'value']);
        $json = $response->toJson();

        $decoded = json_decode($json, true);
        $this->assertSame(true, $decoded['success']);
        $this->assertSame('OK', $decoded['message']);
        $this->assertSame(['key' => 'value'], $decoded['data']);
    }

    #[Test]
    public function test_response_payload_json_serializable(): void
    {
        $response = ApiResponse::success('test');
        $encoded = json_encode($response);

        $decoded = json_decode($encoded, true);
        $this->assertSame(true, $decoded['success']);
        $this->assertSame('test', $decoded['data']);
    }

    #[Test]
    public function test_response_payload_stringable(): void
    {
        $response = ApiResponse::success('hello');
        $string = (string) $response;

        $this->assertIsString($string);
        $decoded = json_decode($string, true);
        $this->assertSame('hello', $decoded['data']);
    }

    #[Test]
    public function test_with_meta_merges_metadata(): void
    {
        $response = ApiResponse::success(['id' => 1])
            ->withMeta(['request_id' => 'abc-123']);

        $this->assertSame(['request_id' => 'abc-123'], $response->meta);

        $array = $response->toArray();
        $this->assertArrayHasKey('meta', $array);
        $this->assertSame('abc-123', $array['meta']['request_id']);
    }

    #[Test]
    public function test_with_meta_merges_with_existing_meta(): void
    {
        $response = ApiResponse::paginated([['id' => 1]], total: 10, page: 1, perPage: 5)
            ->withMeta(['request_id' => 'abc-123']);

        $this->assertArrayHasKey('pagination', $response->meta);
        $this->assertSame('abc-123', $response->meta['request_id']);
        $this->assertSame(10, $response->meta['pagination']['total']);
    }

    #[Test]
    public function test_with_headers_attaches_headers(): void
    {
        $response = ApiResponse::success()
            ->withHeaders(['X-Request-Id' => 'abc-123', 'X-Rate-Limit' => '100']);

        $this->assertSame('abc-123', $response->headers['X-Request-Id']);
        $this->assertSame('100', $response->headers['X-Rate-Limit']);
    }

    #[Test]
    public function test_with_headers_merges_with_existing_headers(): void
    {
        $response = ApiResponse::success()
            ->withHeaders(['X-Request-Id' => 'abc-123'])
            ->withHeaders(['X-Rate-Limit' => '100']);

        $this->assertSame('abc-123', $response->headers['X-Request-Id']);
        $this->assertSame('100', $response->headers['X-Rate-Limit']);
    }

    #[Test]
    public function test_with_status_code_overrides_status(): void
    {
        $response = ApiResponse::success(['id' => 1])
            ->withStatusCode(202);

        $this->assertSame(202, $response->statusCode);
        $this->assertTrue($response->success);
    }

    #[Test]
    public function test_with_methods_return_new_instances(): void
    {
        $original = ApiResponse::success(['id' => 1]);

        $withMeta = $original->withMeta(['key' => 'value']);
        $withHeaders = $original->withHeaders(['X-Custom' => 'test']);
        $withStatus = $original->withStatusCode(202);

        $this->assertNotSame($original, $withMeta);
        $this->assertNotSame($original, $withHeaders);
        $this->assertNotSame($original, $withStatus);

        // Original is unchanged
        $this->assertNull($original->meta);
        $this->assertSame([], $original->headers);
        $this->assertSame(200, $original->statusCode);
    }

    #[Test]
    public function test_fluent_chaining(): void
    {
        $response = ApiResponse::success(['id' => 1])
            ->withMeta(['request_id' => 'abc'])
            ->withHeaders(['X-Custom' => 'header'])
            ->withStatusCode(202);

        $this->assertInstanceOf(ResponsePayload::class, $response);
        $this->assertSame(202, $response->statusCode);
        $this->assertSame('abc', $response->meta['request_id']);
        $this->assertSame('header', $response->headers['X-Custom']);
        $this->assertSame(['id' => 1], $response->data);
    }

    #[Test]
    public function test_unauthorized_response(): void
    {
        $response = ApiResponse::unauthorized();

        $this->assertFalse($response->success);
        $this->assertSame('Unauthorized', $response->message);
        $this->assertSame(401, $response->statusCode);
        $this->assertNull($response->errors);

        $errors = ['reason' => 'token expired'];
        $custom = ApiResponse::unauthorized('Token expired', $errors);
        $this->assertSame('Token expired', $custom->message);
        $this->assertSame($errors, $custom->errors);
    }

    #[Test]
    public function test_forbidden_response(): void
    {
        $response = ApiResponse::forbidden();

        $this->assertFalse($response->success);
        $this->assertSame('Forbidden', $response->message);
        $this->assertSame(403, $response->statusCode);
        $this->assertNull($response->errors);

        $errors = ['reason' => 'insufficient scope'];
        $custom = ApiResponse::forbidden('No access', $errors);
        $this->assertSame('No access', $custom->message);
        $this->assertSame($errors, $custom->errors);
    }

    #[Test]
    public function test_accepted_response(): void
    {
        $response = ApiResponse::accepted();

        $this->assertTrue($response->success);
        $this->assertSame('Accepted', $response->message);
        $this->assertSame(202, $response->statusCode);
        $this->assertNull($response->data);

        $data = ['job_id' => 'abc-123'];
        $custom = ApiResponse::accepted($data, 'Queued');
        $this->assertSame('Queued', $custom->message);
        $this->assertSame($data, $custom->data);
        $this->assertSame(202, $custom->statusCode);
    }

    #[Test]
    public function test_internal_server_error_response(): void
    {
        $response = ApiResponse::internalServerError();

        $this->assertFalse($response->success);
        $this->assertSame('Internal Server Error', $response->message);
        $this->assertSame(500, $response->statusCode);
        $this->assertNull($response->errors);

        $errors = ['trace' => 'xyz'];
        $custom = ApiResponse::internalServerError('Database down', $errors);
        $this->assertSame('Database down', $custom->message);
        $this->assertSame($errors, $custom->errors);
    }

    #[Test]
    public function test_with_pagination_merges_pagination_meta(): void
    {
        $response = ApiResponse::success([['id' => 1], ['id' => 2]])
            ->withPagination(total: 50, page: 2, perPage: 10);

        $this->assertNotNull($response->meta);
        $this->assertArrayHasKey('pagination', $response->meta);
        $this->assertSame(50, $response->meta['pagination']['total']);
        $this->assertSame(2, $response->meta['pagination']['page']);
        $this->assertSame(10, $response->meta['pagination']['per_page']);
        $this->assertSame(5, $response->meta['pagination']['last_page']);
    }

    #[Test]
    public function test_with_pagination_preserves_existing_meta(): void
    {
        $response = ApiResponse::success(['x'])
            ->withMeta(['request_id' => 'abc'])
            ->withPagination(total: 25, page: 1, perPage: 10);

        $this->assertSame('abc', $response->meta['request_id']);
        $this->assertSame(3, $response->meta['pagination']['last_page']);
    }

    #[Test]
    public function test_with_pagination_handles_zero_per_page(): void
    {
        $response = ApiResponse::success([])->withPagination(total: 10, page: 1, perPage: 0);

        $this->assertSame(10, $response->meta['pagination']['last_page']);
    }

    #[Test]
    public function test_default_headers_is_empty_array(): void
    {
        $response = ApiResponse::success();

        $this->assertSame([], $response->headers);
    }
}
